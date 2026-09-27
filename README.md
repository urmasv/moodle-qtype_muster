# qtype_muster ("Muster")

*[Eesti keel](#qtype_muster-muster) | [English](#qtype_muster-muster-1)*

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

---

# qtype_muster ("Muster")

*[Eesti keel](#qtype_muster-muster) | [English](#qtype_muster-muster-1)*

A Moodle question type where the student fills a teacher-defined grid,
one cell at a time, using colours and/or characters defined by the
teacher (or loaded from an administrator-created base set). The question
is intended for creative/free-form work and is always graded manually,
as a binary pass/fail.

## Features

- The teacher sets the grid's width and height (in cells) and the cell
  side length in pixels; the administrator can cap the maximum grid size
  allowed.
- The palette consists of colours and/or characters/symbols; rows can be
  reordered by drag-and-drop, each row's type (colour/symbol) is chosen
  separately, and the matching input field is shown conditionally.
- The administrator can create reusable **base sets** (ready-made
  colour/symbol collections) that a teacher can **add** to their own
  question's palette (never an automatic replacement), and can then
  freely edit after adding.
- Optional **background image**: placed under the whole grid at 30%
  opacity; the cell size (not the image's dimensions) determines the
  grid's size. Filled cells are always fully opaque. Both the respondent
  and the grader can toggle the background image on/off independently;
  when off, the grid looks exactly as it would without a background
  image.
- The teacher can see and comment on the student's work while it is
  still **in progress** (not yet submitted) - this uses a dedicated
  `qtype/muster:comment` capability and an AJAX-driven comment block that
  does not depend on the attempt's submission state.
- Grading is always manual (`qbehaviour_manualgraded`): the teacher can
  optionally comment, then mark the whole attempt as pass or fail.

## Requirements

- Moodle 5.0 or later (`$plugin->requires = 2025041400`). Developed and
  tested with a Moodle 5.2 environment in mind.

## Installation

1. Copy the plugin's contents into the `question/type/muster/` directory
   of your Moodle installation.
2. Open "Site administration > Notifications" to run the database
   installation routine.
3. If needed, configure the maximum grid size and/or add colour/symbol
   base sets under "Site administration > Plugins > Question types >
   Muster".

## Administrator settings and capabilities

- **Maximum grid size** (`qtype_muster/maxgriddimension`, default 30) -
  limits how large a grid a teacher can create.
- **Base set management** (`question/type/muster/managepresets.php`) - a
  separate page (not an $ADMIN tree node), access gated by the
  `moodle/question:config` capability.
- **`qtype/muster:comment`** - the capability to add comments to Muster
  question attempts (including in-progress ones); granted by default to
  the editingteacher, teacher and manager roles at the module context
  level.
