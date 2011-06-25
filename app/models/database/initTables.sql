USE netteDBdevel;
CREATE TABLE adresa(
    id INT NOT NULL AUTO_INCREMENT,
    organizacia VARCHAR(64),
    ulica VARCHAR(64) NOT NULL,
    psc VARCHAR(8) NOT NULL,
    mesto VARCHAR(64) NOT NULL,
    stat VARCHAR(64) NOT NULL,
    PRIMARY KEY (id)
) ENGINE INNODB;
CREATE TABLE osoba (
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

CREATE TABLE skola (
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

CREATE TABLE typ_studia (
    id INT NOT NULL AUTO_INCREMENT,
    nazov VARCHAR(64),
    skratka VARCHAR(5),
    dlzka TINYINT,
    maturitny_rocnik TINYINT,
    PRIMARY KEY (id)
) ENGINE INNODB;

CREATE TABLE riesitel (
    id INT NOT NULL AUTO_INCREMENT,
    osoba_id INT NOT NULL,
    skola_id INT NOT NULL,
    rok_maturity SMALLINT NOT NULL,
    typ_studia_id INT NOT NULL,
    telefon_rodic VARCHAR(255),
    koresp_adresa_id INT NOT NULL,
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
