-- Run once on an existing database after checking for duplicate titles.
-- This preserves service IDs, prices, and admission history.
UPDATE services SET title = TRIM(title) WHERE BINARY title <> BINARY TRIM(title);
ALTER TABLE services ADD UNIQUE KEY uq_services_title (title);
