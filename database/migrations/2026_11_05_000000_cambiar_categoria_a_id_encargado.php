<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categoria')) {
            return;
        }

        DB::statement('ALTER TABLE categoria ADD COLUMN id_encargado INT UNSIGNED NULL AFTER descripcion');

        DB::statement(<<<'SQL'
            INSERT INTO encargado_plato (nombre, apellido, cargo, telefono)
            SELECT DISTINCT LEFT(TRIM(c.nombre_encargado), 80), '', NULL, NULL
            FROM categoria c
            WHERE TRIM(c.nombre_encargado) <> ''
              AND NOT EXISTS (
                  SELECT 1
                  FROM encargado_plato e
                  WHERE e.nombre = LEFT(TRIM(c.nombre_encargado), 80)
              )
        SQL);

        DB::statement(<<<'SQL'
            UPDATE categoria c
            SET c.id_encargado = (
                SELECT MIN(e.id_encargado)
                FROM encargado_plato e
                WHERE e.nombre = LEFT(TRIM(c.nombre_encargado), 80)
                   OR CONCAT_WS(' ', e.nombre, NULLIF(e.apellido, '')) = TRIM(c.nombre_encargado)
            )
        SQL);

        DB::statement(<<<'SQL'
            INSERT INTO encargado_plato (nombre, apellido, cargo, telefono)
            SELECT 'Sin asignar', '', NULL, NULL
            WHERE NOT EXISTS (
                SELECT 1 FROM encargado_plato WHERE nombre = 'Sin asignar'
            )
            AND EXISTS (
                SELECT 1 FROM categoria WHERE id_encargado IS NULL
            )
        SQL);

        DB::statement(<<<'SQL'
            UPDATE categoria
            SET id_encargado = (
                SELECT MIN(id_encargado) FROM encargado_plato WHERE nombre = 'Sin asignar'
            )
            WHERE id_encargado IS NULL
        SQL);

        DB::statement('ALTER TABLE categoria DROP COLUMN nombre_encargado');
        DB::statement('ALTER TABLE categoria MODIFY COLUMN id_encargado INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE categoria ADD INDEX idx_categoria_encargado (id_encargado)');
        DB::statement('ALTER TABLE categoria ADD CONSTRAINT fk_categoria_encargado FOREIGN KEY (id_encargado) REFERENCES encargado_plato (id_encargado) ON DELETE RESTRICT ON UPDATE CASCADE');
    }

    public function down(): void
    {
        if (! Schema::hasTable('categoria') || ! Schema::hasColumn('categoria', 'id_encargado')) {
            return;
        }

        DB::statement('ALTER TABLE categoria DROP FOREIGN KEY fk_categoria_encargado');
        DB::statement('ALTER TABLE categoria DROP INDEX idx_categoria_encargado');
        DB::statement("ALTER TABLE categoria ADD COLUMN nombre_encargado VARCHAR(100) NOT NULL DEFAULT '' AFTER descripcion");

        DB::statement(<<<'SQL'
            UPDATE categoria c
            JOIN encargado_plato e ON e.id_encargado = c.id_encargado
            SET c.nombre_encargado = LEFT(TRIM(CONCAT_WS(' ', e.nombre, NULLIF(e.apellido, ''))), 100)
        SQL);

        DB::statement('ALTER TABLE categoria DROP COLUMN id_encargado');
    }
};
