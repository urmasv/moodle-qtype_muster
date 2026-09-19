# qtype_muster ("Muster")

Moodle'i küsimusetüüp, kus õpilane täidab õpetaja määratud suurusega
ruudustikku ükshaaval, kasutades õpetaja (või administraatori loodud
baasvaliku) määratud värve ja/või kirjamärke. Küsimus on mõeldud
loomingulise/vabavormilise töö jaoks ja hinnatakse alati käsitsi,
binaarselt (arvestatud / mittearvestatud).

## Omadused

- Õpetaja määrab ruudustiku laiuse ja kõrguse (ruutudes) ning ruudu külje
  pikkuse pikslites; administraator saab piirata maksimaalset lubatud
  ruudustiku suurust.
- Palett koosneb värvidest ja/või kirjamärkidest/sümbolitest; ridade
  järjekorda saab muuta lohistades, iga rea tüüp (värv/sümbol) valitakse
  eraldi ja vastav sisendväli kuvatakse tingimuslikult.
- Administraator saab luua taaskasutatavaid **baasvalikuid** (värvide/
  sümbolite valmiskomplekte), mida õpetaja saab oma küsimuse paletti
  **lisada** (mitte kunagi automaatselt asendada) ja mida saab pärast
  lisamist vabalt muuta.
- Valikuline **taustapilt**: kogu ruudustiku alla, 30% opaaksusega; ruudu
  suurus (mitte pildi mõõtmed) määrab ruudustiku suuruse. Täidetud ruudud
  on alati täisopaaksed. Vastaja ja hindaja saavad taustapildi eraldi
  sisse/välja lülitada; väljalülitatuna näeb ruudustik täpselt samasugune
  välja kui ilma taustapildita.
- Õpetaja näeb ja saab kommenteerida ka **pooleliolevat** (esitamata)
  õpilase tööd - selleks on eraldi õigus `qtype/muster:comment` ja AJAX
  kaudu töötav kommentaariplokk, mis ei sõltu katse esitamise olekust.
- Hindamine on alati käsitsi (`qbehaviour_manualgraded`): õpetaja saab
  vajadusel kommenteerida, seejärel märkida terve töö arvestatuks või
  mittearvestatuks.

## Nõuded

- Moodle 5.0 või uuem (`$plugin->requires = 2025041400`). Arendatud ja
  testitud silmas pidades Moodle 5.2 keskkonda.

## Paigaldamine

1. Kopeeri plugina sisu kataloogi `question/type/muster/` oma Moodle'i
   paigalduses.
2. Ava „Site administration > Notifications", et käivitada andmebaasi
   paigaldusrutiin.
3. Vajadusel seadista „Site administration > Plugins > Question types >
   Muster" alt maksimaalne ruudustiku suurus ja/või lisa värvide/sümbolite
   baasvalikuid.

## Administraatori seaded ja õigused

- **Ruudustiku maksimaalne suurus** (`qtype_muster/maxgriddimension`,
  vaikimisi 30) - piirab, kui suure ruudustiku õpetaja saab luua.
- **Baasvalikute haldus** (`question/type/muster/managepresets.php`) -
  eraldi leht (pole $ADMIN puu sõlm), ligipääs õigusega
  `moodle/question:config`.
- **`qtype/muster:comment`** - õigus lisada kommentaare mustri küsimuse
  katsetele (sh poolelioleavtele); vaikimisi antud rollidele
  editingteacher, teacher ja manager mooduli tasandil.

## Teadaolevad piirangud (enne tootmiskasutust üle vaadata)

Plugin on hetkel `MATURITY_ALPHA` (versioon 0.1.0). Järgnevad piirangud on
koodis endas kommentaaridena dokumenteeritud, kuid vajavad kinnitamist
päris-Moodle keskkonnas enne avalikku/tootmislikku kasutust:

- **Varundamine/taastamine**: tabel `qtype_muster_comments` (õpetaja
  kommentaarid katsetele) EI ole kaetud kursuse backup/restore
  mehhanismiga - kommentaarid lähevad kursuse varundamisel/taastamisel
  kaduma. Ainult küsimuse enda definitsioon (ruudustiku mõõtmed, palett,
  taustapilt) on kaetud.
- **Privacy API (GDPR)**: `classes/privacy/provider.php` katab ainult
  kommentaari AUTORI (õpetaja) andmed. Kommentaari SUBJEKT (õpilane,
  kelle tööd kommenteeriti) ei ole otseselt kaetud - kui see on nõutav,
  tuleb lisada liitpäring läbi vastava tegevusmooduli.
- **Kommentaaride õiguste kontroll** väljundis (`renderer.php`,
  `get_current_context()`) kasutab lihtsustatult `$PAGE->context` - tuleb
  üle kontrollida küsimuse eelvaate (preview) ja katse ülevaate (review)
  eri kontekstide korral.
- **Lohistamisega ümberjärjestamise JS** (`amd/src/reorder.js`) on
  testitud ainult käsitsi, päris-Moodle keskkonnas ekraanipiltide kaudu -
  automatiseeritud teste sellele pole kirjutatud.
- **Kommentaarid on ainult lihttekst** (FORMAT_PLAIN, tavaline
  `<textarea>`) - rikastekst ega pildi lisamine kommentaarina pole
  toetatud.

## Litsents

GNU General Public License v3 või uuem - vt fail `LICENSE`.
