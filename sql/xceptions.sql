DROP TABLE IF EXISTS xceptions;
CREATE TABLE xceptions (
  xcId int(11) NOT NULL AUTO_INCREMENT,
  xcYear int(11) NOT NULL DEFAULT '0',
  xcMonth int(11) NOT NULL DEFAULT '0',
  xcDay int(11) NOT NULL DEFAULT '0',
  xcNewMonth int(11) NOT NULL DEFAULT '0',
  xcNewDay int(11) NOT NULL DEFAULT '0',
  xcNote varchar(255) DEFAULT NULL,
  xcFlag tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (xcId)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO xceptions VALUES (0, 2016, 3, 8, 3, 9, '(Note: The service for the 40 Martyrs is held today.)', 0);
INSERT INTO xceptions VALUES (0, 2016, 3, 9, 99, 9, '(Note: The service for the 40 Martyrs is transferred to March 8.)', 0);
INSERT INTO xceptions VALUES (0, 2016, 4, 25, 99, 99, '(Note: The service for St Mark is transferred to May 4.)', 0);
INSERT INTO xceptions VALUES (0, 2016, 5, 4, 4, 25, '(Note: The service for St Mark is held today.)', 0);
INSERT INTO xceptions VALUES (0, 2016, 4, 30, 99, 99, '(Note: The service for St James is transferred to May 5.)', 0);
INSERT INTO xceptions VALUES (0, 2016, 5, 5, 4, 30, '(Note: The service for St James is held today.)', 0);
INSERT INTO xceptions VALUES (0, 2016, 6, 19, 99, 99, '(Note: The service for St Jude is transferred to June 20.)', 0);
INSERT INTO xceptions VALUES (0, 2016, 6, 20, 6, 19, '(Note: The service for St Jude is held today.)', 0);
insert into xceptions values(0, 2017, 2, 23, 2, 24, '(Note: The service for the Forerunner is held today.)', 0);
insert into xceptions values(0, 2017, 2, 24, 99, 99, '(Note: The service for the Forerunner is transferred to February 23.)', 0);
insert into xceptions values(0, 2017, 2, 27, 99, 99, '(Note: The service for St Raphael is transferred to March 4.)', 0);
insert into xceptions values(0, 2017, 3, 4, 2, 27, '(Note: The service for St Raphael is held today.)', 0);
insert into xceptions values(0, 2017, 6, 11, 99, 99, '(Note: The service for the Apostles is transferred to June 12.)', 0);
insert into xceptions values(0, 2017, 6, 12, 6, 11, '(Note: The service for the Apostles is held today.)', 0);
insert into xceptions values(0, 2017, 8, 12, 8, 13, '(Note: The service for St Tikhon is held today.)', 0);
insert into xceptions values(0, 2017, 8, 13, 99, 99, '(Note: The service for St Tikhon is transferred to August 12.)', 0);
INSERT INTO xceptions VALUES (0, 2018, 4, 7, 99, 99, '(Note: The service for St Tikhon is transferred to April 10.)', 0);
INSERT INTO xceptions VALUES (0, 2018, 4, 10, 4, 7, '(Note: The service for St Tikhon is held today.)', 0);
