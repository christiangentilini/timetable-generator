-- Aggiungi la colonna last_login se non esiste
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login DATETIME DEFAULT NULL;

-- Aggiorna il timestamp per gli utenti esistenti
UPDATE users SET last_login = NOW() WHERE last_login IS NULL; 