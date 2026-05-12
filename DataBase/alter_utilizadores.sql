-- Adicionar coluna preferencias à tabela utilizadores
ALTER TABLE utilizadores ADD COLUMN preferencias JSON DEFAULT NULL;