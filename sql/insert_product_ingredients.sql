-- Insert product-ingredient relationships
-- Hot Coffee drinks: Coffee (15g) + Milk (150ml) + Syrup (10ml) + Sweeteners (5g)
INSERT INTO product_ingredients (product_id, ingredient_id, quantity_required) VALUES
-- Spanish Latte - TRUTH (id: 1)
(1, 2, 15),    -- Coffee
(1, 1, 150),   -- Milk
(1, 3, 10),    -- Syrup
(1, 4, 5),     -- Sweeteners

-- Spanish Latte - UNITY (id: 2)
(2, 2, 15),    -- Coffee
(2, 1, 150),   -- Milk
(2, 3, 10),    -- Syrup
(2, 4, 5),     -- Sweeteners

-- Macchiato - TRUTH (id: 3)
(3, 2, 15),    -- Coffee
(3, 1, 100),   -- Milk (less milk for macchiato)
(3, 3, 5),     -- Syrup
(3, 4, 3),     -- Sweeteners

-- Macchiato - UNITY (id: 4)
(4, 2, 15),    -- Coffee
(4, 1, 100),   -- Milk
(4, 3, 5),     -- Syrup
(4, 4, 3),     -- Sweeteners

-- Hazelnut Latte - TRUTH (id: 5)
(5, 2, 15),    -- Coffee
(5, 1, 150),   -- Milk
(5, 3, 15),    -- Syrup (more syrup for hazelnut)
(5, 4, 5),     -- Sweeteners

-- Hazelnut Latte - UNITY (id: 6)
(6, 2, 15),    -- Coffee
(6, 1, 150),   -- Milk
(6, 3, 15),    -- Syrup
(6, 4, 5),     -- Sweeteners

-- Americano - TRUTH (id: 7)
(7, 2, 20),    -- Coffee (more coffee, no milk)
(7, 4, 2),     -- Sweeteners

-- Americano - UNITY (id: 8)
(8, 2, 20),    -- Coffee
(8, 4, 2),     -- Sweeteners

-- Caramel Macchiato - TRUTH (id: 9)
(9, 2, 15),    -- Coffee
(9, 1, 100),   -- Milk
(9, 3, 20),    -- Syrup (more for caramel)
(9, 4, 5),     -- Sweeteners

-- Caramel Macchiato - UNITY (id: 10)
(10, 2, 15),   -- Coffee
(10, 1, 100),  -- Milk
(10, 3, 20),   -- Syrup
(10, 4, 5),    -- Sweeteners

-- Vanilla Latte - TRUTH (id: 11)
(11, 2, 15),   -- Coffee
(11, 1, 150),  -- Milk
(11, 3, 12),   -- Syrup
(11, 4, 5),    -- Sweeteners

-- Vanilla Latte - UNITY (id: 12)
(12, 2, 15),   -- Coffee
(12, 1, 150),  -- Milk
(12, 3, 12),   -- Syrup
(12, 4, 5),    -- Sweeteners

-- Iced Drinks: Same ingredients but slightly different quantities for iced version
-- Iced Spanish Latte - UNITY (id: 13)
(13, 2, 15),   -- Coffee
(13, 1, 150),  -- Milk
(13, 3, 10),   -- Syrup
(13, 4, 5),    -- Sweeteners

-- Iced Spanish Latte - LOVE (id: 14)
(14, 2, 15),   -- Coffee
(14, 1, 150),  -- Milk
(14, 3, 10),   -- Syrup
(14, 4, 5),    -- Sweeteners

-- Iced Macchiato - UNITY (id: 15)
(15, 2, 15),   -- Coffee
(15, 1, 100),  -- Milk
(15, 3, 5),    -- Syrup
(15, 4, 3),    -- Sweeteners

-- Iced Macchiato - LOVE (id: 16)
(16, 2, 15),   -- Coffee
(16, 1, 100),  -- Milk
(16, 3, 5),    -- Syrup
(16, 4, 3),    -- Sweeteners

-- Iced Hazelnut Latte - UNITY (id: 17)
(17, 2, 15),   -- Coffee
(17, 1, 150),  -- Milk
(17, 3, 15),   -- Syrup
(17, 4, 5),    -- Sweeteners

-- Iced Hazelnut Latte - LOVE (id: 18)
(18, 2, 15),   -- Coffee
(18, 1, 150),  -- Milk
(18, 3, 15),   -- Syrup
(18, 4, 5),    -- Sweeteners

-- Iced Americano - UNITY (id: 19)
(19, 2, 20),   -- Coffee
(19, 4, 2),    -- Sweeteners

-- Iced Americano - LOVE (id: 20)
(20, 2, 20),   -- Coffee
(20, 4, 2),    -- Sweeteners

-- Iced Caramel Macchiato - UNITY (id: 21)
(21, 2, 15),   -- Coffee
(21, 1, 100),  -- Milk
(21, 3, 20),   -- Syrup
(21, 4, 5),    -- Sweeteners

-- Iced Caramel Macchiato - LOVE (id: 22)
(22, 2, 15),   -- Coffee
(22, 1, 100),  -- Milk
(22, 3, 20),   -- Syrup
(22, 4, 5),    -- Sweeteners

-- Iced Vanilla Latte - UNITY (id: 23)
(23, 2, 15),   -- Coffee
(23, 1, 150),  -- Milk
(23, 3, 12),   -- Syrup
(23, 4, 5),    -- Sweeteners

-- Iced Vanilla Latte - LOVE (id: 24)
(24, 2, 15),   -- Coffee
(24, 1, 150),  -- Milk
(24, 3, 12),   -- Syrup
(24, 4, 5),    -- Sweeteners

-- Milk Series (using Milk + Syrup + Sweeteners)
-- Strawberry Milk - UNITY (id: 25)
(25, 1, 200),  -- Milk
(25, 3, 20),   -- Syrup (strawberry)
(25, 4, 8),    -- Sweeteners

-- Strawberry Milk - LOVE (id: 26)
(26, 1, 200),  -- Milk
(26, 3, 20),   -- Syrup
(26, 4, 8),    -- Sweeteners

-- Strawberry Hazelnut - UNITY (id: 27)
(27, 1, 200),  -- Milk
(27, 3, 25),   -- Syrup (strawberry + hazelnut)
(27, 4, 8),    -- Sweeteners

-- Strawberry Hazelnut - LOVE (id: 28)
(28, 1, 200),  -- Milk
(28, 3, 25),   -- Syrup
(28, 4, 8),    -- Sweeteners

-- Choco Milk - UNITY (id: 29)
(29, 1, 200),  -- Milk
(29, 3, 20),   -- Syrup (chocolate)
(29, 4, 8),    -- Sweeteners

-- Choco Milk - LOVE (id: 30)
(30, 1, 200),  -- Milk
(30, 3, 20),   -- Syrup
(30, 4, 8),    -- Sweeteners

-- Fruit Sodas (using Syrup + Sweeteners only - no milk/coffee)
-- Strawberry Fruit Soda (id: 31)
(31, 3, 30),   -- Syrup
(31, 4, 10),   -- Sweeteners

-- Blueberry Fruit Soda (id: 32)
(32, 3, 30),   -- Syrup
(32, 4, 10),   -- Sweeteners

-- Green Apple Fruit Soda (id: 33)
(33, 3, 30),   -- Syrup
(33, 4, 10),   -- Sweeteners

-- Blue Lemonade Fruit Soda (id: 34)
(34, 3, 30),   -- Syrup
(34, 4, 10),   -- Sweeteners

-- Lychee Fruit Soda (id: 35)
(35, 3, 30),   -- Syrup
(35, 4, 10),   -- Sweeteners

-- Four Season Fruit Soda (id: 36)
(36, 3, 30),   -- Syrup
(36, 4, 10);   -- Sweeteners
