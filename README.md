# Bsky-Bot für admin.ch-Mitteilungen

Lädt RSS-Feed von admin.ch und postet die Links mit dem Titel auf Bluesky unter [@bundesrat.piit.ch](https://bsky.app/profile/bundesrat.piit.ch).
Speichert die veröffentlichten Links als md5-Hash in `hashes.txt`.
Benötigt `.env`-Datei mit folgenden Daten.

```
BSKY_IDENTIFIER="bundesrat.piit.ch"
BSKY_PW="xxxx-xxxx-xxxx-xxxx"
```
