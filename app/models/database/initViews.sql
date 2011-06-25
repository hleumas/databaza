USE netteDBdevel;
CREATE VIEW zoznamy_riesitel_view AS
SELECT riesitel.id, osoba.meno, osoba.priezvisko, osoba.datum_narodenia,
       osoba.email, osoba.telefon, osoba.jabber, riesitel.telefon_rodic,
       riesitel.koresp_kam, riesitel.rok_maturity, skola.skratka skola_skratka,
       adresa.mesto, typ_studia.skratka typ_studia

FROM riesitel
    LEFT JOIN osoba      ON riesitel.osoba_id         = osoba.id
    LEFT JOIN skola      ON riesitel.skola_id         = skola.id
    LEFT JOIN adresa     ON osoba.adresa_id           = adresa.id
    LEFT JOIN typ_studia ON riesitel.typ_studia_id    = typ_studia.id;
