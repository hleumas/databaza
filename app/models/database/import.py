#!/usr/bin/python
# vim: set fileencoding=utf-8 :

import MySQLdb
import string

def insertInto(db, table, items):
    keys, values = zip(*items.items())
    query = ('INSERT INTO ' + unicode(table) + '(' + ', '.join(map(unicode, keys)) + ')'
          + 'VALUES("' + '", "'.join(map(unicode, values)) + '")')

    #print query
    db.query(query.encode('utf-8'));
    #db.commit();

def lastID(db):
    db2.query("SELECT LAST_INSERT_ID()");
    return db2.store_result().fetch_row(0, 0)[0][0];

def partition(s):
    if s is None:
        return []

    return [
             elem for elem in 
             [''.join(w.split()) for w in s.split(',')]
             if elem is not ''
           ]


    
db1=MySQLdb.connect('www.fks.sk', 'databaza_user', 'ENei7gah',
                   'fks_databaza_current', use_unicode=True, charset='utf8')
db2=MySQLdb.connect('www.fks.sk', 'devel_user', 'qNaBP8NSe3dwRurb',
                   'netteDBdevel', use_unicode=True, charset='utf8')

db1.query("SELECT * FROM skoly")

r = db1.store_result()
d = r.fetch_row(0, 1)

adresySkol = dict()

for skola in d:
    items = {
              'organizacia': skola['nazov'],
              'ulica'      : skola['adresa_ulica'],
              'mesto'      : skola['adresa_mesto'],
              'stat'       : u'Slovensko',
              'psc'        : skola['adresa_psc'],
            }
    insertInto(db2, 'adresa', items)
    adresaID = lastID(db2)
    adresySkol[skola['id']] = adresaID;

    items = {
              'id'       : skola['id'],
              'nazov'    : skola['nazov'],
              'skratka'  : skola['skratka'],
              'adresa_id': adresaID,
              'zakladna' : skola['zakladna_skola'],
              'stredna'  : skola['stredna_skola'],
            }
    insertInto(db2, 'skola', items)

db1.query('SELECT * FROM riesitelia')

r = db1.store_result()
d = r.fetch_row(0, 1)



studia=[
        {'id': 1, 'nazov': u'Neštudent', 'skratka': u'N/A', 'dlzka': 0, 'maturitny_rocnik': 0},
        {'id': 4, 'nazov': u'Stredná škola', 'skratka': u'SŠ', 'dlzka': 4, 'maturitny_rocnik': 4},
        {'id': 5, 'nazov': u'Päťročná stredná škola', 'skratka': u'SŠ5r', 'dlzka': 5, 'maturitny_rocnik': 5},
        {'id': 6, 'nazov': u'Šesťročná stredná škola', 'skratka': u'SŠ6r', 'dlzka': 6, 'maturitny_rocnik': 6},
        {'id': 8, 'nazov': u'Osemročné gymnázium', 'skratka': u'Gym', 'dlzka': 8, 'maturitny_rocnik': 8},
        {'id': 13, 'nazov': u'Základná škola', 'skratka': u'ZŠ', 'dlzka': 9, 'maturitny_rocnik': 13},
       ]

for typ in studia:
    insertInto(db2, 'typ_studia', typ);

for riesitel in d:
    insertInto(db2, 'adresa', {
                                'ulica': riesitel['adresa_ulica'],
                                'mesto': riesitel['adresa_mesto'],
                                'stat' : u'Slovensko',
                                'psc'  : riesitel['adresa_psc'],
                              })

    if riesitel['typ_studia'] == 0:
        riesitel['typ_studia'] = '1';

    adresaID = lastID(db2)

    items = {
              'id'             : riesitel['id'],
              'meno'           : riesitel['meno'],
              'priezvisko'     : riesitel['priezvisko'],
              'datum_narodenia': riesitel['datum_narodenia'],
              'adresa_id'      : adresaID,
              'email'          : riesitel['e_mail'],
              'telefon'        : riesitel['telefon'],
              'jabber'         : riesitel['im'],
            }
    insertInto(db2, 'osoba', items)

    if riesitel['koresp_kam'] == 2:
        insertInto(db2, 'adresa', {
                                    'ulica': riesitel['koresp_ulica'],
                                    'mesto': riesitel['koresp_mesto'],
                                    'stat' : u'Slovensko',
                                    'psc'  : riesitel['koresp_psc'],
                                  })
    korespAdresa = {
                     0: adresaID,
                     1: adresySkol[riesitel['skola']],
                     2: lastID(db2),
                   }
    items = {
              'id'              : riesitel['id'],
              'osoba_id'        : riesitel['id'],
              'telefon_rodic'   : riesitel['telefon_rodica'],
              'skola_id'        : riesitel['skola'],
              'rok_maturity'    : riesitel['rok_maturity'],
              'typ_studia_id'   : riesitel['typ_studia'],
              'koresp_kam'      : riesitel['koresp_kam'],
              'koresp_adresa_id': korespAdresa[riesitel['koresp_kam']],
              'sustredeni'      : riesitel['sustredeni'],
              'vyhier'          : 0,
              'celostatiek'     : 0,
            }
    insertInto(db2, 'riesitel', items)


db1.query('SELECT * FROM login')
r = db1.store_result()
d = r.fetch_row(0, 1)

for login in d:
    insertInto(db2, 'users', {
        'id'       : login['id'],
        'login'    : login['login'],
        'salt'     : login['salt'],
        'password' : login['password'],
        'active'   : login['enabled'],
        })
    
db2.commit();
