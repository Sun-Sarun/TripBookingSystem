-- 1. ADDRESS DATA (10 Records)
INSERT INTO `address` (`country`, `province`, `district`, `street`, `houseNumber`) VALUES
('Vietnam', 'Quang Ninh', 'Ha Long City', 'Bai Chay', 'Block 5'),
('Vietnam', 'Lao Cai', 'Sa Pa', 'Muong Hoa', '12'),
('Thailand', 'Phuket', 'Kathu', 'Patong Beach Rd', '101'),
('Thailand', 'Chiang Mai', 'Mueang', 'Nimmanahaeminda Rd', '25'),
('Indonesia', 'Bali', 'Badung', 'Jl. Pantai Kuta', 'S-1'),
('Indonesia', 'West Nusa Tenggara', 'Lombok', 'Senggigi St', '99'),
('Cambodia', 'Siem Reap', 'Svay Dangkum', 'Pub Street', '07'),
('Malaysia', 'Selangor', 'Batu Caves', 'Lebuhraya Selayang', 'KM13'),
('Philippines', 'Palawan', 'El Nido', 'Serena St', '22'),
('Singapore', 'Central', 'Marina Bay', '10 Bayfront Ave', 'L1');

-- 2. ACCOUNT DATA (10 Records)
INSERT INTO `account` (`email`, `password`, `permission`) VALUES
('nguyen.an@email.vn', '$2y$10$vI82Ot...', 'user'),
('somchai.p@email.th', '$2y$10$tZ92Kl...', 'user'),
('wayan.bali@email.id', '$2y$10$mB33Jq...', 'user'),
('siti.nur@email.my', '$2y$10$pX55Rr...', 'user'),
('chen.wei@email.sg', '$2y$10$aQ11Ww...', 'user'),
('admin.hanoi@trip.com', '$2y$10$zY99Pp...', 'admin'),
('maria.santos@email.ph', '$2y$10$kL88Ee...', 'user'),
('rath.sovann@email.kh', '$2y$10$hH44Gg...', 'user'),
('vinh.tran@email.vn', '$2y$10$fF66Dd...', 'user'),
('kanya.s@email.th', '$2y$10$sS33Aa...', 'user');

-- 3. SPOT DATA (10 Records)
INSERT INTO `spot` (`name`, `type`, `status`, `phone`, `addressID`, `detail`, `price`, `discount`, `photo`) VALUES
('Ha Long Bay Cruise', 'Tour', 'Open', '+84-24-3926', 1, 'Overnight cruise among limestone karsts.', 150.00, 15.00, 'halong.jpg'),
('Fansipan Legend', 'Mountain', 'Open', '+84-214-381', 2, 'Cable car to the highest peak in Indochina.', 35.00, 0.00, 'fansipan.png'),
('Patong Beach Resort', 'Hotel', 'Available', '+66-76-340', 3, 'Beachfront accommodation in Phuket.', 120.00, 20.00, 'patong.jpg'),
('Doi Suthep Temple', 'Temple', 'Open', '+66-53-295', 4, 'Sacred mountain temple overlooking Chiang Mai.', 5.00, 0.00, 'doisuthep.jpg'),
('Kuta Surf School', 'Activity', 'Open', '+62-361-751', 5, 'Beginner surfing lessons in Bali.', 45.00, 5.00, 'surf.jpg'),
('Gili Trawangan Tour', 'Island', 'Open', '+62-370-693', 6, 'Speedboat trip to the Gili islands.', 60.00, 0.00, 'gili.jpg'),
('Angkor Night Market', 'Shopping', 'Open', '+855-63-966', 7, 'Traditional crafts and local street food.', 0.00, 0.00, 'market.jpg'),
('Batu Caves Tour', 'Culture', 'Open', '+60-3-6189', 8, 'Iconic rainbow stairs and limestone caves.', 2.00, 0.00, 'batu.jpg'),
('El Nido Island Hopping', 'Tour', 'Open', '+63-48-433', 9, 'Visit Big Lagoon and Secret Beach.', 30.00, 2.00, 'elnido.jpg'),
('Marina Bay Sands Deck', 'Landmark', 'Open', '+65-6688', 10, 'Observation deck with city skyline views.', 25.00, 0.00, 'mbs.jpg');

-- 4. USERINFO DATA (10 Records)
-- Removed redundant 'email' column as it is already in the 'account' table
INSERT INTO `userinfo` (`accountID`, `FName`, `LName`, `gender`, `DOB`, `phone`, `createdDate`) VALUES
(1, 'An', 'Nguyen', 'Male', '1992-04-12', '+84912345678', '2025-01-10'),
(2, 'Somchai', 'Phetsri', 'Male', '1985-08-22', '+66898765432', '2025-01-11'),
(3, 'Wayan', 'Saputra', 'Male', '1998-12-01', '+62812345678', '2025-01-12'),
(4, 'Siti', 'Nurhaliza', 'Female', '1990-05-15', '+60123456789', '2025-01-13'),
(5, 'Wei', 'Chen', 'Male', '1993-02-28', '+6598765432', '2025-01-14'),
(7, 'Maria', 'Santos', 'Female', '1989-11-03', '+63917123456', '2025-01-15'),
(8, 'Sovann', 'Rath', 'Male', '1996-07-19', '+85512345678', '2025-01-16'),
(9, 'Vinh', 'Tran', 'Male', '2000-01-01', '+84900112233', '2025-01-17'),
(10, 'Kanya', 'Srisai', 'Female', '1994-09-10', '+66855443322', '2025-01-18'),
(6, 'Admin', 'User', 'Other', '1980-01-01', '+84240000000', '2025-01-01');

-- 5. PAYMENTINFO DATA (10 Records)
-- Removed 'cvv' column for security/PCI compliance alignment with schema
INSERT INTO `paymentInfo` (`userID`, `paymentType`, `cardCode`, `expireDate`) VALUES
(1, 'Credit Card', '4111222233334444', '2027-12-31'),
(2, 'Debit Card', '5555666677778888', '2026-06-15'),
(3, 'Credit Card', '4222333344445555', '2028-01-20'),
(4, 'E-Wallet', 'WALLET-998877', '2029-12-31'),
(5, 'Credit Card', '4333444455556666', '2026-03-10'),
(6, 'Credit Card', '4888777766665555', '2027-09-09'),
(7, 'Debit Card', '5111222233334444', '2026-11-30'),
(8, 'Credit Card', '4000111122223333', '2027-05-05'),
(9, 'E-Wallet', 'WALLET-112233', '2028-08-08'),
(10, 'Credit Card', '4777888899990000', '2026-02-28');

-- 6. BOOKING DATA (10 Records)
INSERT INTO `booking` (`accountID`, `spotID`, `purchaseDate`, `unit`, `paymentID`, `checkinDate`, `checkoutDate`, `totalPrice`) VALUES
(1, 1, '2025-01-20', 2, 1, '2025-03-01', '2025-03-02', 270.00),
(2, 3, '2025-01-21', 1, 2, '2025-02-14', '2025-02-16', 120.00),
(3, 5, '2025-01-22', 2, 3, '2025-03-10', '2025-03-10', 80.00),
(4, 8, '2025-01-23', 4, 4, '2025-04-05', '2025-04-05', 8.00),
(5, 10, '2025-01-24', 2, 5, '2025-05-20', '2025-05-20', 50.00),
(7, 9, '2025-01-25', 1, 7, '2025-03-15', '2025-03-15', 28.00),
(8, 7, '2025-01-26', 1, 8, '2025-02-01', '2025-02-01', 0.00),
(9, 2, '2025-01-27', 3, 9, '2025-12-24', '2025-12-24', 105.00),
(10, 4, '2025-01-28', 2, 10, '2025-06-12', '2025-06-12', 10.00),
(1, 6, '2025-01-29', 2, 1, '2025-03-05', '2025-03-05', 120.00);