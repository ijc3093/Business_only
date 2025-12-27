<?php
/**
 * Controller.php
 * - Single PDO connection
 * - Admin + Users register
 * - Login supports old hashes (md5/sha256/sha384) + new (password_hash)
 */

class Controller
{
    private PDO $dbh;

    private string $server = "localhost";
    private string $username = "root";
    private string $password = "root";
    private string $dbname = "gospel";

    public function __construct()
    {
        try {
            $this->dbh = new PDO(
                "mysql:host={$this->server};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            // In production, log this instead of echo
            die("Database could not be connected: " . $e->getMessage());
        }
    }

    // ---------------------------
    // Helpers
    // ---------------------------
    public function pdo(): PDO
    {
        return $this->dbh;
    }

    private function hashMatches(string $plain, string $dbHash): bool
    {
        // 1) New secure hash (bcrypt/argon) stored by password_hash()
        if (password_get_info($dbHash)['algo'] !== 0) {
            return password_verify($plain, $dbHash);
        }

        // 2) Legacy hashes
        if (hash('sha256', $plain) === $dbHash) return true;
        if (hash('sha384', $plain) === $dbHash) return true; // your SQL seed looks like this length
        if (md5($plain) === $dbHash) return true;

        return false;
    }

    private function upgradePasswordIfNeeded(int $idadmin, string $plain, string $dbHash): void
    {
        // If already password_hash(), do nothing
        if (password_get_info($dbHash)['algo'] !== 0) {
            return;
        }

        // Upgrade old hashes to password_hash for better security
        $newHash = password_hash($plain, PASSWORD_DEFAULT);
        $sql = "UPDATE admin SET password = :password WHERE idadmin = :id";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([
            ':password' => $newHash,
            ':id' => $idadmin
        ]);
    }

    // ---------------------------
    // Admin: Find existing
    // ---------------------------
    public function findAdminByEmailOrUsername(string $email, string $username)
    {
        $sql = "SELECT * FROM admin WHERE email = :email OR username = :username LIMIT 1";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':username' => $username
        ]);
        $row = $stmt->fetch();
        return $row ?: false;
    }

    // ---------------------------
    // Admin: Register (admin/register.php uses this)
    // ---------------------------
    public function registerAdmin(array $data): bool
    {
        $sql = "INSERT INTO admin (username, email, password, gender, mobile, designation, role, image, status)
                VALUES (:username, :email, :password, :gender, :mobile, :designation, :role, :image, :status)";
        $stmt = $this->dbh->prepare($sql);

        return $stmt->execute([
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':password' => $data['password'], // store password_hash() result
            ':gender' => $data['gender'],
            ':mobile' => $data['mobile'],
            ':designation' => $data['designation'],
            ':role' => $data['role'],
            ':image' => $data['image'],
            ':status' => $data['status'],
        ]);
    }

    // ---------------------------
    // Users: Register (root /register.php uses this)
    // ---------------------------
    public function registerUser(array $data): bool
    {
        $sql = "INSERT INTO users (name, email, password, gender, mobile, designation, image, status)
                VALUES (:name, :email, :password, :gender, :mobile, :designation, :image, :status)";
        $stmt = $this->dbh->prepare($sql);

        return $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':password' => $data['password'], // store password_hash() result
            ':gender' => $data['gender'],
            ':mobile' => $data['mobile'],
            ':designation' => $data['designation'],
            ':image' => $data['image'],
            ':status' => $data['status'],
        ]);
    }

    public function findUserByEmail(string $email)
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: false;
    }


        private function userHashMatches(string $plain, string $dbHash): bool
    {
        // New secure hash (bcrypt/argon)
        if (password_get_info($dbHash)['algo'] !== 0) {
            return password_verify($plain, $dbHash);
        }

        // Legacy hashes
        if (hash('sha256', $plain) === $dbHash) return true;
        if (hash('sha384', $plain) === $dbHash) return true;
        if (md5($plain) === $dbHash) return true;

        return false;
    }

    private function upgradeUserPasswordIfNeeded(int $userId, string $plain, string $dbHash): void
    {
        // Already password_hash => nothing to do
        if (password_get_info($dbHash)['algo'] !== 0) {
            return;
        }

        // Upgrade to password_hash()
        $newHash = password_hash($plain, PASSWORD_DEFAULT);

        $sql = "UPDATE users SET password = :p WHERE id = :id LIMIT 1";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([
            ':p'  => $newHash,
            ':id' => $userId
        ]);
    }

    // ---------------------------
    // Admin Login (admin/index.php calls this)
    // ---------------------------
    // ---------------------------
    // Admin Login (NO session write)
    // returns admin row or null
    // ---------------------------
    public function adminLogin(string $usernameOrEmail, string $password): ?array
    {
        $sql = "SELECT idadmin, username, email, password, role, status, image
                FROM admin
                WHERE username = :u OR email = :e
                LIMIT 1";

        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([
            ':u' => $usernameOrEmail,
            ':e' => $usernameOrEmail,
        ]);

        $row = $stmt->fetch();
        if (!$row) return null;

        if ((int)$row['status'] !== 1) return null;

        $dbHash = (string)$row['password'];
        if (!$this->hashMatches($password, $dbHash)) return null;

        // Upgrade old hashes to password_hash()
        $this->upgradePasswordIfNeeded((int)$row['idadmin'], $password, $dbHash);

        // Return row only (no session!)
        return [
            'idadmin' => (int)$row['idadmin'],
            'username' => (string)$row['username'],
            'email' => (string)$row['email'],
            'role' => (int)$row['role'],
            'image' => (string)($row['image'] ?? 'default.jpg'),
        ];
    }

    public function userLogin(string $email, string $password): array|false
    {
        $sql = "SELECT id, name, email, password, image, status
                FROM users
                WHERE email = :email
                LIMIT 1";

        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) return false;
        if ((int)$user['status'] !== 1) return false;

        $dbHash = (string)$user['password'];

        // ✅ Verify password_hash() or legacy md5
        $ok = false;

        if (password_get_info($dbHash)['algo'] !== 0) {
            $ok = password_verify($password, $dbHash);
        } else {
            // legacy md5
            $ok = (md5($password) === $dbHash);

            // ✅ Auto-upgrade old md5 to password_hash after successful login
            if ($ok) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $up = $this->dbh->prepare("UPDATE users SET password = :p WHERE id = :id");
                $up->execute([
                    ':p'  => $newHash,
                    ':id' => (int)$user['id']
                ]);
                $user['password'] = $newHash;
            }
        }

        return $ok ? $user : false;
    }


    // ---------------------------
    // Notification helper
    // ---------------------------
    public function addNotification(string $notiuser, string $notireceiver, string $notitype): bool
    {
        $sql = "INSERT INTO notification (notiuser, notireceiver, notitype, is_read)
                VALUES (:u, :r, :t, 0)";
        $stmt = $this->dbh->prepare($sql);

        $ok = $stmt->execute([
            ':u' => $notiuser,
            ':r' => $notireceiver,
            ':t' => $notitype
        ]);

        // Email alert (only if receiver looks like an email)
        if ($ok && filter_var($notireceiver, FILTER_VALIDATE_EMAIL)) {
            $mailer = __DIR__ . '/../includes/mailer.php';
            if (file_exists($mailer)) {
                require_once $mailer;

                $subject = "New Notification";
                $message = "From: <b>" . htmlspecialchars($notiuser) . "</b><br>"
                        . "Type: <b>" . htmlspecialchars($notitype) . "</b><br>"
                        . "Login to view it.";

                sendNotificationEmail($notireceiver, $subject, $message);
            }
        }

        return $ok;
    }




    // Return role record: ['idrole'=>..., 'name'=>...]
    public function getRoleById(int $idrole): ?array
    {
        $stmt = $this->dbh->prepare("SELECT idrole, name FROM role WHERE idrole = :id LIMIT 1");
        $stmt->execute([':id' => $idrole]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getAllRoles(): array
    {
        $stmt = $this->dbh->prepare("SELECT idrole, name FROM role ORDER BY idrole ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
