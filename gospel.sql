DROP DATABASE IF EXISTS gospel;

CREATE DATABASE IF NOT EXISTS gospel;

SHOW DATABASES;

USE gospel;

--
-- Table structure for table `admin`
--
CREATE TABLE `admin`(
    `idadmin` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `password` varchar(200) NOT NULL,
    `gender` varchar(50) NOT NULL,
    `mobile` varchar(50) NOT NULL,
    `designation` varchar(50) NOT NULL,
    `role` INT NULL,
    `image` varchar(50) NOT NULL,
    `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status` int(10) NOT NULL,
    PRIMARY KEY (`idadmin`),
  INDEX `role_id` (`role` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


INSERT INTO `admin`(`idadmin`, `username`, `email`, `password`, `gender`, `mobile`, `designation`, `role`, `image`,`time`, `status`) 
VALUES(1, 'admin', 'admin@admin.com', '9ae2be73b58b565bce3e47493a56e26aecd71870d1963316a97e3ac3408c9835ad8cf0f3c1bc703527c30265534f75ae', 'male', '8945568975', 'admin', 1, 'ball2.jpg','2021-11-08 05:24:00pm', 1);
--
-- Dumping data for table `admin`
--
-- --------------------------------------------------------------------

--
-- Table structure for table `role`
--
CREATE TABLE IF NOT EXISTS `role` (
  `idrole` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`idrole`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC));

INSERT INTO `role` (`name`) values ('admin'),('manager'),('gospel'),('Staff');


--
-- Table structure for table `deleteduser`
--
CREATE TABLE `deleteduser`(
    `id` int(11) NOT NULL,
    `email` varchar(50) NOT NULL,
    `deltime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
-- ---------------------------------------------------------------------

--
-- Table structure for table `feedback`
--
CREATE TABLE `feedback`(
    `id` int(11) NOT NULL,
    `sender` varchar(50) NOT NULL,
    `receiver` varchar(50) NOT NULL,
    `title` varchar(100) NOT NULL,
    `feedbackdata` varchar(500) NOT NULL,
    `attachment` varchar(50) NOT NULL,
    `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
-- ---------------------------------------------------------------------

--
-- Table structure for table `deletedfeedback`
--
CREATE TABLE `deletedfeedback`(
    `id` int(11) NOT NULL,
    `email` varchar(50) NOT NULL,
    `deltime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
-- ---------------------------------------------------------------------

--
-- Table structure for table `notification`
--
CREATE TABLE `notification`(
    `id` int(11) NOT NULL,
    `notiuser` varchar(50) NOT NULL,
    `notireceiver` varchar(50) NOT NULL,
    `notitype` varchar(50) NOT NULL,
    `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ---------------------------------------------------------------------

--
-- Table structure for table `users`
--
CREATE TABLE `users` (
    `id` int(11) NOT NULL,
    `name` varchar(50) NOT NULL,
    `email` varchar(50) NOT NULL,
    `password` varchar(50) NOT NULL,
    `gender` varchar(50) NOT NULL,
    `mobile` varchar(50) NOT NULL,
    `designation` varchar(50) NOT NULL,
    `image` varchar(50) NOT NULL,
    `status` int(10) NOT NULL,
    `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
--
-- Indexs for dumped tables
--

--
-- Indexs for table `admin`
-- 
-- ALTER TABLE `admin`
--     ADD PRIMARY KEY (`id`);

--
-- Indexs for table `role`
-- 
-- ALTER TABLE `role`
--     ADD PRIMARY KEY (`idrole`);
    
--
-- Indexs for table `deleteduser`
--
ALTER TABLE `deleteduser`
    ADD PRIMARY KEY (`id`);


--
-- Indexs for table `feedback`
--
ALTER TABLE `feedback`
    ADD PRIMARY KEY (`id`);


--
-- Indexs for table `notification`
--
ALTER TABLE `notification`
    ADD PRIMARY KEY (`id`);


--
-- Indexs for table `user`
--
ALTER TABLE `users`
    ADD PRIMARY KEY (`id`);


--
-- AUTO_INCREMENT for dumped tables
--

--
--  AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
    MODIFY `idadmin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;


--
--  AUTO_INCREMENT for table `admin`
--
ALTER TABLE `role`
    MODIFY `idrole` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
--  AUTO_INCREMENT for table `deleteduser`
--
ALTER TABLE `deleteduser`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;


--
--  AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;


--
--  AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;


--
--  AUTO_INCREMENT for table `user`
--
ALTER TABLE `users`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;


COMMIT;