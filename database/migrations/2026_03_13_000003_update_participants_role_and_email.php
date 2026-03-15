<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Amplía el enum 'role' de participants para incluir todos los estamentos
     * de Uniguajira y Comunidad Externa. Además hace email nullable y añade
     * student_code (único, nullable) para Estudiante / Graduado.
     *
     * Roles: Estudiante | Docente | Administrativo | Graduado | Comunidad Externa
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            /*
             * SQLite no soporta modificar constraints ni añadir columnas unique
             * fácilmente. Reconstruimos la tabla conservando todos los datos.
             */
            DB::statement('PRAGMA foreign_keys = OFF');

            DB::statement('CREATE TABLE participants_new (
                "id"               integer       not null primary key autoincrement,
                "document"         varchar(20)   not null unique,
                "student_code"     varchar(20)   null unique,
                "first_name"       varchar(100)  not null,
                "last_name"        varchar(100)  not null,
                "email"            varchar       null unique,
                "role"             varchar       not null default \'Comunidad Externa\'
                                   check ("role" in (
                                       \'Estudiante\',\'Docente\',\'Administrativo\',
                                       \'Graduado\',\'Comunidad Externa\'
                                   )),
                "sexo"             varchar(30)   null,
                "grupo_priorizado" varchar(30)   null,
                "affiliation"      varchar       null
                                   check ("affiliation" in (
                                       \'Catedratico\',\'Ocasional\',\'Planta\'
                                   )),
                "program_id"       integer       null
                                   references "programs"("id") on delete cascade,
                "created_at"       datetime      null,
                "updated_at"       datetime      null
            )');

            // Copia los datos existentes; student_code queda NULL para registros previos
            DB::statement('
                INSERT INTO participants_new
                    (id, document, student_code, first_name, last_name, email, role,
                     sexo, grupo_priorizado, affiliation, program_id, created_at, updated_at)
                SELECT
                    id,
                    document,
                    CASE WHEN typeof(student_code) != \'null\' THEN student_code ELSE NULL END,
                    first_name,
                    last_name,
                    email,
                    role,
                    sexo,
                    grupo_priorizado,
                    affiliation,
                    program_id,
                    created_at,
                    updated_at
                FROM participants
            ');

            DB::statement('DROP TABLE participants');
            DB::statement('ALTER TABLE participants_new RENAME TO participants');

            DB::statement('PRAGMA foreign_keys = ON');

        } elseif ($driver === 'mysql') {
            // Agregar student_code solo si no existe (compatible con MySQL 5.7+)
            if (! Schema::hasColumn('participants', 'student_code')) {
                DB::statement("ALTER TABLE participants ADD COLUMN student_code varchar(20) NULL AFTER document");
                DB::statement("ALTER TABLE participants ADD UNIQUE INDEX participants_student_code_unique (student_code)");
            }
            // MODIFY es idempotente: si ya están correctos, no cambia nada
            DB::statement("ALTER TABLE participants MODIFY COLUMN email varchar(255) NULL");
            DB::statement("ALTER TABLE participants MODIFY COLUMN role ENUM('Estudiante','Docente','Administrativo','Graduado','Comunidad Externa') NOT NULL DEFAULT 'Comunidad Externa'");
        } else {
            // PostgreSQL
            DB::statement("ALTER TABLE participants ADD COLUMN IF NOT EXISTS student_code varchar(20) NULL");
            DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS participants_student_code_unique ON participants(student_code) WHERE student_code IS NOT NULL");
            DB::statement("ALTER TABLE participants ALTER COLUMN email DROP NOT NULL");
            DB::statement("ALTER TABLE participants DROP CONSTRAINT IF EXISTS participants_role_check");
            DB::statement("ALTER TABLE participants ADD CONSTRAINT participants_role_check
                CHECK (role IN ('Estudiante','Docente','Administrativo','Graduado','Comunidad Externa'))");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            DB::statement('CREATE TABLE participants_old (
                "id"               integer       not null primary key autoincrement,
                "document"         varchar(20)   not null unique,
                "first_name"       varchar(100)  not null,
                "last_name"        varchar(100)  not null,
                "email"            varchar       not null unique,
                "role"             varchar       not null
                                   check ("role" in (\'Estudiante\',\'Docente\')),
                "sexo"             varchar(30)   null,
                "grupo_priorizado" varchar(30)   null,
                "affiliation"      varchar       null
                                   check ("affiliation" in (
                                       \'Catedratico\',\'Ocasional\',\'Planta\'
                                   )),
                "program_id"       integer       null
                                   references "programs"("id") on delete cascade,
                "created_at"       datetime      null,
                "updated_at"       datetime      null
            )');

            DB::statement("
                INSERT INTO participants_old
                SELECT id, document, first_name, last_name, email, role, sexo,
                       grupo_priorizado, affiliation, program_id, created_at, updated_at
                FROM participants
                WHERE role IN ('Estudiante','Docente') AND email IS NOT NULL
            ");

            DB::statement('DROP TABLE participants');
            DB::statement('ALTER TABLE participants_old RENAME TO participants');
            DB::statement('PRAGMA foreign_keys = ON');

        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE participants DROP COLUMN IF EXISTS student_code");
            DB::statement("
                ALTER TABLE participants
                MODIFY COLUMN email varchar(255) NOT NULL,
                MODIFY COLUMN role  ENUM('Estudiante','Docente') NOT NULL
            ");
        }
    }
};
