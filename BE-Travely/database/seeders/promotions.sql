-- Insert sample promotions with code
INSERT INTO `promotion` (`code`, `description`, `discount`, `startDate`, `endDate`, `quantity`) 
VALUES 
('SUMMER2025', 'Giảm giá mùa hè 2025 - 20%', 20.00, '2025-12-01', '2025-12-31', 100),
('NEWYEAR2026', 'Chào đón năm mới 2026 - 15%', 15.00, '2025-12-01', '2026-01-15', 50),
('WINTER10', 'Ưu đãi mùa đông - 10%', 10.00, '2025-12-01', '2026-02-28', 200);
