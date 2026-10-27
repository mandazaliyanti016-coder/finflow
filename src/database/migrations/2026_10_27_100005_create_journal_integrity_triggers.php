<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
        CREATE OR REPLACE FUNCTION journal_check_balance() RETURNS trigger AS $$
        DECLARE d bigint; c bigint; n int;
        BEGIN
            SELECT COALESCE(SUM(debit),0), COALESCE(SUM(credit),0), COUNT(*)
              INTO d, c, n FROM journal_lines WHERE journal_entry_id = NEW.journal_entry_id;
            IF n < 2 OR d <> c THEN
                RAISE EXCEPTION 'Jurnal % tidak seimbang atau kurang dari 2 baris (debit=%, kredit=%)',
                    NEW.journal_entry_id, d, c;
            END IF;
            RETURN NULL;
        END;
        $$ LANGUAGE plpgsql;

        CREATE CONSTRAINT TRIGGER trg_journal_lines_balance
            AFTER INSERT ON journal_lines
            DEFERRABLE INITIALLY DEFERRED
            FOR EACH ROW EXECUTE FUNCTION journal_check_balance();

        CREATE OR REPLACE FUNCTION journal_entry_requires_lines() RETURNS trigger AS $$
        BEGIN
            IF NOT EXISTS (SELECT 1 FROM journal_lines WHERE journal_entry_id = NEW.id) THEN
                RAISE EXCEPTION 'Jurnal % tidak memiliki baris', NEW.id;
            END IF;
            RETURN NULL;
        END;
        $$ LANGUAGE plpgsql;

        CREATE CONSTRAINT TRIGGER trg_journal_entries_has_lines
            AFTER INSERT ON journal_entries
            DEFERRABLE INITIALLY DEFERRED
            FOR EACH ROW EXECUTE FUNCTION journal_entry_requires_lines();

        CREATE OR REPLACE FUNCTION journal_immutable() RETURNS trigger AS $$
        BEGIN
            RAISE EXCEPTION 'Jurnal bersifat immutable; gunakan jurnal pembalik';
        END;
        $$ LANGUAGE plpgsql;

        CREATE TRIGGER trg_journal_lines_immutable
            BEFORE UPDATE OR DELETE ON journal_lines
            FOR EACH ROW EXECUTE FUNCTION journal_immutable();

        CREATE TRIGGER trg_journal_entries_immutable
            BEFORE UPDATE OR DELETE ON journal_entries
            FOR EACH ROW EXECUTE FUNCTION journal_immutable();
        SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
        DROP TRIGGER IF EXISTS trg_journal_entries_immutable ON journal_entries;
        DROP TRIGGER IF EXISTS trg_journal_lines_immutable ON journal_lines;
        DROP TRIGGER IF EXISTS trg_journal_entries_has_lines ON journal_entries;
        DROP TRIGGER IF EXISTS trg_journal_lines_balance ON journal_lines;
        DROP FUNCTION IF EXISTS journal_immutable();
        DROP FUNCTION IF EXISTS journal_entry_requires_lines();
        DROP FUNCTION IF EXISTS journal_check_balance();
        SQL);
    }
};
