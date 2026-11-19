CREATE DATABASE IF NOT EXISTS ppdb_sekolah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ppdb_sekolah;

CREATE TABLE IF NOT EXISTS calon_siswa (
    id INT NOT NULL AUTO_INCREMENT,
    workspace_id CHAR(32) NOT NULL,
    nama VARCHAR(64) NOT NULL,
    alamat VARCHAR(255) NOT NULL,
    jenis_kelamin VARCHAR(16) NOT NULL,
    agama VARCHAR(16) NOT NULL,
    sekolah_asal VARCHAR(64) NOT NULL,
    created_at DATETIME NOT NULL,
    foto MEDIUMBLOB NULL,
    foto_mime VARCHAR(16) NULL,
    foto_nama VARCHAR(40) NULL,
    PRIMARY KEY (id),
    INDEX siswa_workspace (workspace_id, id)
) ENGINE=InnoDB;
