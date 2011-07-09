USE netteDBdevel;
CREATE TABLE IF NOT EXISTS adresa(
    id INT NOT NULL AUTO_INCREMENT,
    organizacia VARCHAR(64),
    ulica VARCHAR(64) NOT NULL,
    psc VARCHAR(8) NOT NULL,
    mesto VARCHAR(64) NOT NULL,
    stat VARCHAR(64) NOT NULL,
    PRIMARY KEY (id)
) ENGINE INNODB;
CREATE TABLE IF NOT EXISTS osoba (
    id INT NOT NULL AUTO_INCREMENT,
    meno VARCHAR(64) NOT NULL,
    priezvisko VARCHAR(64) NOT NULL,
    datum_narodenia DATE,
    adresa_id INT NOT NULL,
    email VARCHAR(255),
    telefon VARCHAR(255),
    jabber VARCHAR(255),
    poznamka VARCHAR(255),
    PRIMARY KEY (id),
    INDEX (adresa_id),
    FOREIGN KEY (adresa_id) REFERENCES adresa(id)
) ENGINE INNODB;

CREATE TABLE IF NOT EXISTS skola (
    id INT NOT NULL AUTO_INCREMENT,
    nazov VARCHAR(64) NOT NULL,
    skratka VARCHAR(32) NOT NULL,
    adresa_id INT NOT NULL,
    telefon VARCHAR(255),
    email VARCHAR(255),
    stredna BOOLEAN,
    zakladna BOOLEAN,
    PRIMARY KEY (id),
    INDEX(adresa_id),
    FOREIGN KEY (adresa_id) REFERENCES adresa(id)
) ENGINE INNODB;

CREATE TABLE IF NOT EXISTS typ_studia (
    id INT NOT NULL AUTO_INCREMENT,
    nazov VARCHAR(64),
    skratka VARCHAR(5),
    dlzka TINYINT,
    maturitny_rocnik TINYINT,
    PRIMARY KEY (id)
) ENGINE INNODB;

CREATE TABLE IF NOT EXISTS riesitel (
    id INT NOT NULL AUTO_INCREMENT,
    osoba_id INT NOT NULL,
    skola_id INT NOT NULL,
    rok_maturity SMALLINT NOT NULL,
    typ_studia_id INT NOT NULL,
    telefon_rodic VARCHAR(255),
    koresp_adresa_id INT,
    koresp_kam TINYINT NOT NULL,
    PRIMARY KEY (id),
    INDEX(osoba_id),
    INDEX(rok_maturity),
    FOREIGN KEY (osoba_id) REFERENCES osoba(id),
    FOREIGN KEY (skola_id) REFERENCES skola(id),
    FOREIGN KEY (typ_studia_id) REFERENCES typ_studia(id),
    INDEX(koresp_adresa_id),
    FOREIGN KEY (koresp_adresa_id) REFERENCES adresa(id)
) ENGINE INNODB;

CREATE TABLE IF NOT EXISTS seria (
    id INT NOT NULL AUTO_INCREMENT,
    semester_id INT NOT NULL,
    cislo SMALLINT NOT NULL,
    termin DATE,
    PRIMARY KEY (id)
) ENGINE INNODB;

CREATE TABLE IF NOT EXISTS semester (
    id INT NOT NULL AUTO_INCREMENT,
    rok SMALLINT NOT NULL,
    cast SMALLINT NOT NULL,
    kategoria_id INT NOT NULL,
    aktualna_seria_id INT,
    PRIMARY KEY (id),
    FOREIGN KEY (aktualna_seria_id) REFERENCES seria(id)
) ENGINE INNODB;

CREATE TABLE IF NOT EXISTS kategoria (
    id INT NOT NULL AUTO_INCREMENT,
    nazov VARCHAR(16),
    aktualny_semester_id INT,
    PRIMARY KEY (id),
    FOREIGN KEY (aktualny_semester_id) REFERENCES semester(id)
) ENGINE INNODB;

ALTER TABLE seria
    ADD FOREIGN KEY (semester_id) REFERENCES semester(id);
ALTER TABLE semester
    ADD FOREIGN KEY (kategoria_id) REFERENCES kategoria(id);

CREATE TABLE IF NOT EXISTS priklad (
    id INT NOT NULL AUTO_INCREMENT,
    nazov VARCHAR(64) NOT NULL,
    opravovatel VARCHAR(64) NOT NULL,
    vzorakovac VARCHAR(64) NOT NULL,
    poznamka VARCHAR(128) NOT NULL,
    cislo INT NOT NULL,
    body SMALLINT NOT NULL,
    seria_id INT NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (seria_id) REFERENCES seria(id)
);

CREATE TABLE IF NOT EXISTS riesitel_priklady (
    id INT NOT NULL AUTO_INCREMENT,
    priklad_id INT NOT NULL,
    riesitel_id INT NOT NULL,
    body SMALLINT,
    PRIMARY KEY (id),
    FOREIGN KEY (priklad_id) REFERENCES priklad(id),
    FOREIGN KEY (riesitel_id) REFERENCES riesitel(id)
); 

CREATE TABLE IF NOT EXISTS riesitel_seria_bonusy (
    id INT NOT NULL AUTO_INCREMENT,
    riesitel_id INT NOT NULL,
    seria_id INT NOT NULL,
    body SMALLINT NOT NULL,
    kod SMALLINT,
    PRIMARY KEY (id),
    FOREIGN KEY (riesitel_id) REFERENCES riesitel(id),
    FOREIGN KEY (seria_id) REFERENCES seria(id)
);
