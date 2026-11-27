CREATE DATABASE IF NOT EXISTS roadmate
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE roadmate;

DROP VIEW  IF EXISTS ReservationCost;
DROP TABLE IF EXISTS MaintenanceRecord;
DROP TABLE IF EXISTS ReservationStatusHistory;
DROP TABLE IF EXISTS Payment;
DROP TABLE IF EXISTS Reservation;
DROP TABLE IF EXISTS Discount;
DROP TABLE IF EXISTS Vehicle;
DROP TABLE IF EXISTS RentalLocation;
DROP TABLE IF EXISTS VehicleModel;
DROP TABLE IF EXISTS VehicleType;
DROP TABLE IF EXISTS Admin;
DROP TABLE IF EXISTS Customer;
DROP TABLE IF EXISTS UserAccount;

CREATE TABLE UserAccount (
  UserID        INT           NOT NULL AUTO_INCREMENT,
  Email         VARCHAR(255)  NOT NULL,
  PasswordHash  VARCHAR(255)  NOT NULL,
  Role          VARCHAR(10)   NOT NULL,
  CreatedAt     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT pk_useraccount       PRIMARY KEY (UserID),
  CONSTRAINT uq_useraccount_email UNIQUE (Email),
  CONSTRAINT ck_useraccount_role  CHECK (Role IN ('customer', 'admin')),
  CONSTRAINT ck_useraccount_email CHECK (Email LIKE '%_@_%._%')
) ENGINE = InnoDB;

CREATE TABLE Customer (
  CustomerID       INT          NOT NULL AUTO_INCREMENT,
  FirstName        VARCHAR(50)  NOT NULL,
  LastName         VARCHAR(50)  NOT NULL,
  Phone            VARCHAR(20)  NOT NULL,
  DriverLicenseNo  VARCHAR(30)  NOT NULL,
  LicenseExpiry    DATE         NOT NULL,
  UserID           INT          NOT NULL,

  CONSTRAINT pk_customer         PRIMARY KEY (CustomerID),
  CONSTRAINT uq_customer_user    UNIQUE (UserID),
  CONSTRAINT uq_customer_license UNIQUE (DriverLicenseNo),
  CONSTRAINT fk_customer_user FOREIGN KEY (UserID)
    REFERENCES UserAccount (UserID)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE Admin (
  AdminID   INT          NOT NULL AUTO_INCREMENT,
  Name      VARCHAR(100) NOT NULL,
  JobTitle  VARCHAR(100) NULL,
  UserID    INT          NOT NULL,

  CONSTRAINT pk_admin      PRIMARY KEY (AdminID),
  CONSTRAINT uq_admin_user UNIQUE (UserID),
  CONSTRAINT fk_admin_user FOREIGN KEY (UserID)
    REFERENCES UserAccount (UserID)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE VehicleType (
  TypeID       INT          NOT NULL AUTO_INCREMENT,
  TypeName     VARCHAR(50)  NOT NULL,
  Description  VARCHAR(255) NULL,

  CONSTRAINT pk_vehicletype      PRIMARY KEY (TypeID),
  CONSTRAINT uq_vehicletype_name UNIQUE (TypeName)
) ENGINE = InnoDB;

CREATE TABLE VehicleModel (
  ModelID    INT          NOT NULL AUTO_INCREMENT,
  Make       VARCHAR(50)  NOT NULL,
  ModelName  VARCHAR(50)  NOT NULL,
  Year       SMALLINT     NOT NULL,
  FuelType   VARCHAR(20)  NOT NULL,
  Seats      TINYINT      NOT NULL,
  TypeID     INT          NOT NULL,

  CONSTRAINT pk_vehiclemodel       PRIMARY KEY (ModelID),
  CONSTRAINT uq_vehiclemodel       UNIQUE (Make, ModelName, Year),
  CONSTRAINT ck_vehiclemodel_year  CHECK (Year BETWEEN 1990 AND 2100),
  CONSTRAINT ck_vehiclemodel_seats CHECK (Seats BETWEEN 1 AND 15),
  CONSTRAINT ck_vehiclemodel_fuel  CHECK (FuelType IN ('Gasoline', 'Diesel', 'Hybrid', 'Electric')),
  CONSTRAINT fk_vehiclemodel_type FOREIGN KEY (TypeID)
    REFERENCES VehicleType (TypeID)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE = InnoDB;

CREATE TABLE RentalLocation (
  LocationID  INT          NOT NULL AUTO_INCREMENT,
  Name        VARCHAR(100) NOT NULL,
  Street      VARCHAR(100) NOT NULL,
  City        VARCHAR(50)  NOT NULL,
  ZipCode     CHAR(5)      NOT NULL,
  Phone       VARCHAR(20)  NOT NULL,

  CONSTRAINT pk_rentallocation     PRIMARY KEY (LocationID),
  CONSTRAINT ck_rentallocation_zip CHECK (ZipCode REGEXP '^[0-9]{5}$')
) ENGINE = InnoDB;

CREATE TABLE Vehicle (
  VehicleID     INT           NOT NULL AUTO_INCREMENT,
  LicensePlate  VARCHAR(10)   NOT NULL,
  Color         VARCHAR(30)   NOT NULL,
  Mileage       INT           NOT NULL DEFAULT 0,
  DailyRate     DECIMAL(8, 2) NOT NULL,
  Status        VARCHAR(20)   NOT NULL DEFAULT 'Available',
  ModelID       INT           NOT NULL,
  LocationID    INT           NOT NULL,

  CONSTRAINT pk_vehicle         PRIMARY KEY (VehicleID),
  CONSTRAINT uq_vehicle_plate   UNIQUE (LicensePlate),
  CONSTRAINT ck_vehicle_mileage CHECK (Mileage >= 0),
  CONSTRAINT ck_vehicle_rate    CHECK (DailyRate > 0),
  CONSTRAINT ck_vehicle_status  CHECK (Status IN ('Available', 'Rented', 'Maintenance', 'Retired')),
  CONSTRAINT fk_vehicle_model FOREIGN KEY (ModelID)
    REFERENCES VehicleModel (ModelID)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_vehicle_location FOREIGN KEY (LocationID)
    REFERENCES RentalLocation (LocationID)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE = InnoDB;

CREATE TABLE Discount (
  DiscountID     INT           NOT NULL AUTO_INCREMENT,
  Code           VARCHAR(20)   NOT NULL,
  PercentOff     DECIMAL(5, 2) NOT NULL,
  StartDate      DATE          NOT NULL,
  EndDate        DATE          NOT NULL,
  MinRentalDays  INT           NOT NULL DEFAULT 1,
  CreatedBy      INT           NULL,

  CONSTRAINT pk_discount         PRIMARY KEY (DiscountID),
  CONSTRAINT uq_discount_code    UNIQUE (Code),
  CONSTRAINT ck_discount_percent CHECK (PercentOff > 0 AND PercentOff <= 100),
  CONSTRAINT ck_discount_dates   CHECK (EndDate >= StartDate),
  CONSTRAINT ck_discount_mindays CHECK (MinRentalDays >= 1),
  CONSTRAINT fk_discount_admin FOREIGN KEY (CreatedBy)
    REFERENCES Admin (AdminID)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE = InnoDB;

CREATE TABLE Reservation (
  ReservationID     INT          NOT NULL AUTO_INCREMENT,
  PickupDate        DATETIME     NOT NULL,
  ReturnDate        DATETIME     NOT NULL,
  Status            VARCHAR(20)  NOT NULL DEFAULT 'Pending',
  CreatedAt         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CustomerID        INT          NOT NULL,
  VehicleID         INT          NOT NULL,
  PickupLocationID  INT          NOT NULL,
  ReturnLocationID  INT          NOT NULL,
  DiscountID        INT          NULL,

  CONSTRAINT pk_reservation        PRIMARY KEY (ReservationID),
  CONSTRAINT ck_reservation_dates  CHECK (ReturnDate > PickupDate),
  CONSTRAINT ck_reservation_status CHECK (Status IN ('Pending', 'Confirmed', 'PickedUp', 'Completed', 'Cancelled')),
  CONSTRAINT fk_reservation_customer FOREIGN KEY (CustomerID)
    REFERENCES Customer (CustomerID)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_reservation_vehicle FOREIGN KEY (VehicleID)
    REFERENCES Vehicle (VehicleID)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_reservation_pickup FOREIGN KEY (PickupLocationID)
    REFERENCES RentalLocation (LocationID)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_reservation_return FOREIGN KEY (ReturnLocationID)
    REFERENCES RentalLocation (LocationID)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_reservation_discount FOREIGN KEY (DiscountID)
    REFERENCES Discount (DiscountID)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE = InnoDB;

CREATE INDEX idx_reservation_availability
  ON Reservation (VehicleID, PickupDate, ReturnDate);

CREATE TABLE Payment (
  PaymentID      INT            NOT NULL AUTO_INCREMENT,
  Amount         DECIMAL(10, 2) NOT NULL,
  Method         VARCHAR(20)    NOT NULL,
  Status         VARCHAR(20)    NOT NULL DEFAULT 'Pending',
  PaidAt         DATETIME       NULL,
  ReservationID  INT            NOT NULL,

  CONSTRAINT pk_payment        PRIMARY KEY (PaymentID),
  CONSTRAINT ck_payment_amount CHECK (Amount >= 0),
  CONSTRAINT ck_payment_method CHECK (Method IN ('Credit Card', 'Debit Card', 'PayPal', 'Cash')),
  CONSTRAINT ck_payment_status CHECK (Status IN ('Pending', 'Paid', 'Refunded', 'Failed')),
  CONSTRAINT ck_payment_paidat CHECK (Status <> 'Paid' OR PaidAt IS NOT NULL),
  CONSTRAINT fk_payment_reservation FOREIGN KEY (ReservationID)
    REFERENCES Reservation (ReservationID)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE = InnoDB;

CREATE TABLE ReservationStatusHistory (
  ReservationID  INT          NOT NULL,
  ChangeNo       INT          NOT NULL,
  Status         VARCHAR(20)  NOT NULL,
  ChangedAt      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT pk_statushistory        PRIMARY KEY (ReservationID, ChangeNo),
  CONSTRAINT ck_statushistory_no     CHECK (ChangeNo >= 1),
  CONSTRAINT ck_statushistory_status CHECK (Status IN ('Pending', 'Confirmed', 'PickedUp', 'Completed', 'Cancelled')),
  CONSTRAINT fk_statushistory_reservation FOREIGN KEY (ReservationID)
    REFERENCES Reservation (ReservationID)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE MaintenanceRecord (
  RecordID     INT            NOT NULL AUTO_INCREMENT,
  ServiceDate  DATE           NOT NULL,
  Description  VARCHAR(255)   NOT NULL,
  Cost         DECIMAL(10, 2) NOT NULL DEFAULT 0,
  VehicleID    INT            NOT NULL,
  AdminID      INT            NULL,

  CONSTRAINT pk_maintenance      PRIMARY KEY (RecordID),
  CONSTRAINT ck_maintenance_cost CHECK (Cost >= 0),
  CONSTRAINT fk_maintenance_vehicle FOREIGN KEY (VehicleID)
    REFERENCES Vehicle (VehicleID)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_maintenance_admin FOREIGN KEY (AdminID)
    REFERENCES Admin (AdminID)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE = InnoDB;

DROP TRIGGER IF EXISTS trg_reservation_before_insert;
DROP TRIGGER IF EXISTS trg_reservation_before_update;

DELIMITER $$

CREATE TRIGGER trg_reservation_before_insert
BEFORE INSERT ON Reservation
FOR EACH ROW
BEGIN
  DECLARE d_start DATE;
  DECLARE d_end   DATE;
  DECLARE d_min   INT;

  IF NEW.Status <> 'Cancelled' AND EXISTS (
       SELECT 1 FROM Reservation r
       WHERE r.VehicleID = NEW.VehicleID
         AND r.Status <> 'Cancelled'
         AND r.PickupDate < NEW.ReturnDate
         AND NEW.PickupDate < r.ReturnDate) THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'This vehicle is already reserved for an overlapping rental period.';
  END IF;

  IF NEW.DiscountID IS NOT NULL THEN
    SELECT StartDate, EndDate, MinRentalDays INTO d_start, d_end, d_min
      FROM Discount WHERE DiscountID = NEW.DiscountID;

    IF DATE(NEW.CreatedAt) NOT BETWEEN d_start AND d_end THEN
      SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'This discount is expired or not active yet.';
    END IF;

    IF GREATEST(1, CEIL(TIMESTAMPDIFF(MINUTE, NEW.PickupDate, NEW.ReturnDate) / 1440)) < d_min THEN
      SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'The rental is too short for this discount.';
    END IF;
  END IF;
END$$

CREATE TRIGGER trg_reservation_before_update
BEFORE UPDATE ON Reservation
FOR EACH ROW
BEGIN
  DECLARE d_start DATE;
  DECLARE d_end   DATE;
  DECLARE d_min   INT;

  IF NEW.Status <> 'Cancelled' AND EXISTS (
       SELECT 1 FROM Reservation r
       WHERE r.VehicleID = NEW.VehicleID
         AND r.ReservationID <> NEW.ReservationID
         AND r.Status <> 'Cancelled'
         AND r.PickupDate < NEW.ReturnDate
         AND NEW.PickupDate < r.ReturnDate) THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'This vehicle is already reserved for an overlapping rental period.';
  END IF;

  IF NEW.DiscountID IS NOT NULL AND (
       NOT (NEW.DiscountID <=> OLD.DiscountID)
       OR NEW.PickupDate <> OLD.PickupDate
       OR NEW.ReturnDate <> OLD.ReturnDate) THEN
    SELECT StartDate, EndDate, MinRentalDays INTO d_start, d_end, d_min
      FROM Discount WHERE DiscountID = NEW.DiscountID;
    IF DATE(NEW.CreatedAt) NOT BETWEEN d_start AND d_end THEN
      SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'This discount is expired or not active yet.';
    END IF;
    IF GREATEST(1, CEIL(TIMESTAMPDIFF(MINUTE, NEW.PickupDate, NEW.ReturnDate) / 1440)) < d_min THEN
      SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'The rental is too short for this discount.';
    END IF;
  END IF;
END$$

DELIMITER ;

CREATE OR REPLACE VIEW ReservationCost AS
SELECT
  r.ReservationID,
  GREATEST(1, CEIL(TIMESTAMPDIFF(MINUTE, r.PickupDate, r.ReturnDate) / 1440)) AS RentalDays,
  v.DailyRate,
  COALESCE(d.PercentOff, 0) AS PercentOff,
  ROUND(GREATEST(1, CEIL(TIMESTAMPDIFF(MINUTE, r.PickupDate, r.ReturnDate) / 1440))
        * v.DailyRate * (1 - COALESCE(d.PercentOff, 0) / 100), 2) AS TotalCost
FROM Reservation r
JOIN Vehicle v       ON v.VehicleID  = r.VehicleID
LEFT JOIN Discount d ON d.DiscountID = r.DiscountID;
