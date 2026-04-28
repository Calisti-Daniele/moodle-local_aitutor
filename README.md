# 🤖 AI Personal Assistant for Moodle

### Assistente AI Personale per Moodle

> A context-aware AI assistant that lives on every Moodle page, knowing each student's courses, grades, deadlines and certificates.  
> Un assistente AI contestuale presente su ogni pagina Moodle, che conosce i corsi, i voti, le scadenze e i certificati di ogni studente.

![Moodle](https://img.shields.io/badge/Moodle-4.5+-orange?logo=moodle)
![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?logo=php)
![License](https://img.shields.io/badge/License-GPL%20v3-blue)
![Status](https://img.shields.io/badge/Status-Alpha-yellow)
![Author](https://img.shields.io/badge/Author-Daniele%20Calisti-informational)

---

## ✨ Features / Funzionalità

| Feature             | Description / Descrizione                                                                   |
| ------------------- | ------------------------------------------------------------------------------------------- |
| 💬 Floating widget  | Always visible on every page / Sempre visibile su ogni pagina                               |
| 🧠 Context-aware    | Knows courses, grades, deadlines, certificates / Conosce corsi, voti, scadenze, certificati |
| 🦙 Multi-provider   | Ollama (free), OpenAI, Anthropic                                                            |
| 🔍 Auto-diagnostics | One-click setup fixes / Correzioni setup in un click                                        |
| 🌍 Multilingual     | English + Italian / Inglese + Italiano                                                      |
| 🔒 GDPR             | Full data export & deletion / Export e cancellazione dati completi                          |
| 🌙 Dark mode        | Automatic / Automatica                                                                      |
| 📱 Mobile           | Fullscreen on small screens / Fullscreen su schermi piccoli                                 |

---

## 📋 Requirements / Requisiti

|            | Minimum / Minimo |
| ---------- | ---------------- |
| Moodle     | 4.5+             |
| PHP        | 8.1+             |
| MySQL      | 8.0+             |
| MariaDB    | 10.6+            |
| PostgreSQL | 13+              |

---

## 📦 Installation / Installazione

### Method 1 — Moodle Plugin Directory _(recommended / consigliato)_

**EN:** Go to Site Administration → Plugins → Install plugins → search "AI Personal Assistant"  
**IT:** Vai in Amministrazione → Plugin → Installa plugin → cerca "AI Personal Assistant"

### Method 2 — GitHub

```bash
cd /path/to/moodle/local
git clone https://github.com/Calisti-Daniele/moodle-local_aitutor.git aitutor
chmod -R 755 aitutor
chown -R www-data:www-data aitutor
```

**EN:** Then go to Site Administration → Notifications  
**IT:** Poi vai in Amministrazione → Notifiche

---

## ⚙️ Quick Setup / Configurazione rapida

**EN:** After installation, go to:  
**IT:** Dopo l'installazione, vai in:

```
Site Administration → Plugins → Local plugins → AI Personal Assistant
Amministrazione → Plugin → Plugin locali → Assistente AI Personale
```

**EN:** The built-in diagnostics page will guide you through the entire setup.  
**IT:** La pagina diagnostica integrata ti guiderà attraverso l'intero setup.

```
http://yourmoodle.com/local/aitutor/diagnostics.php
```

---

## 🦙 Provider 1 — Ollama _(Free / Gratuito)_

**EN:** Run AI models locally — free, private, no API key needed.  
**IT:** Esegui modelli AI in locale — gratis, privato, nessuna API key.

```bash
# Linux
curl -fsSL https://ollama.com/install.sh | sh
ollama pull llama3.2
ollama pull nomic-embed-text

# macOS
brew install ollama
ollama serve &
ollama pull llama3.2
ollama pull nomic-embed-text
```

**EN:** ⚠️ Important: whitelist Ollama in Moodle HTTP Security settings  
**IT:** ⚠️ Importante: aggiungi Ollama alla whitelist nelle impostazioni Sicurezza HTTP

```
Site Admin → Security → HTTP Security
→ Allowed hosts: localhost (or ollama if Docker)
→ Allowed ports: 11434
```

**EN:** Or just click "Fix automatically" on the diagnostics page!  
**IT:** Oppure clicca "Correggi automaticamente" nella pagina diagnostica!

---

## 🤖 Provider 2 — OpenAI

**EN:** Requires API key from [platform.openai.com](https://platform.openai.com)  
**IT:** Richiede API key da [platform.openai.com](https://platform.openai.com)

| Model         | Cost        | Recommended for                                |
| ------------- | ----------- | ---------------------------------------------- |
| gpt-4o-mini   | 💰 Low      | **Most use cases / La maggior parte dei casi** |
| gpt-4o        | 💰💰 Medium | Complex reasoning / Ragionamento complesso     |
| gpt-3.5-turbo | 💰 Very low | Simple tasks / Compiti semplici                |

---

## 🧠 Provider 3 — Anthropic (Claude)

**EN:** Requires API key from [console.anthropic.com](https://console.anthropic.com)  
**IT:** Richiede API key da [console.anthropic.com](https://console.anthropic.com)

| Model                    | Cost        | Recommended for                |
| ------------------------ | ----------- | ------------------------------ |
| claude-3-5-haiku-latest  | 💰 Low      | **Chat / Conversazione**       |
| claude-3-5-sonnet-latest | 💰💰 Medium | Best quality / Massima qualità |

---

## 🛠️ Troubleshooting

**EN:** Most issues are automatically detected and fixed by the diagnostics page:  
**IT:** La maggior parte dei problemi viene rilevata e corretta automaticamente dalla pagina diagnostica:

```
http://yourmoodle.com/local/aitutor/diagnostics.php
```

**EN:** For detailed guides, see the [Wiki](https://github.com/Calisti-Daniele/moodle-local_aitutor/wiki).  
**IT:** Per guide dettagliate, consulta la [Wiki](https://github.com/Calisti-Daniele/moodle-local_aitutor/wiki).

---

## 🔒 Privacy & GDPR

**EN:** This plugin stores conversation sessions and messages linked to user IDs.  
When using Ollama, **all data stays on your server**.  
When using OpenAI/Anthropic, messages are sent to their APIs — review their privacy policies.

**IT:** Il plugin archivia sessioni di conversazione e messaggi collegati agli ID utente.  
Con Ollama, **tutti i dati rimangono sul tuo server**.  
Con OpenAI/Anthropic, i messaggi vengono inviati alle loro API — consulta le loro privacy policy.

**EN:** Full GDPR tools available in Site Administration → Privacy and policies.  
**IT:** Strumenti GDPR completi disponibili in Amministrazione → Privacy e policy.

---

## 🌍 Translations / Traduzioni

- 🇬🇧 English — complete / completo
- 🇮🇹 Italian / Italiano — complete / completo

**EN:** Want to add your language? Contribute via [AMOS](https://lang.moodle.org).  
**IT:** Vuoi aggiungere la tua lingua? Contribuisci tramite [AMOS](https://lang.moodle.org).

---

## 🤝 Contributing / Contribuire

```bash
git clone https://github.com/Calisti-Daniele/moodle-local_aitutor.git
cd moodle-local_aitutor
git checkout -b feat/my-feature
# make changes
git commit -m "feat: description"
git push origin feat/my-feature
# open Pull Request
```

**EN:** Please follow [Moodle Coding Style](https://moodledev.io/general/development/policies/codingstyle).  
**IT:** Segui il [Moodle Coding Style](https://moodledev.io/general/development/policies/codingstyle).

---

## 📄 License / Licenza

GNU GPL v3 — see [LICENSE](LICENSE) file.

---

## 👤 Author / Autore

**Daniele Calisti**

- GitHub: [@Calisti-Daniele](https://github.com/Calisti-Daniele)
- Moodle: [moodle.org/user/profile.php](https://moodle.org/user/profile.php)

---

## ⭐ Support / Supporto

**EN:** If this plugin helps you:  
**IT:** Se questo plugin ti è utile:

- ⭐ Star the repo on GitHub / Metti una stella su GitHub
- 📝 Leave a review on [Moodle Plugin Directory](https://moodle.org/plugins) / Lascia una recensione sul Plugin Directory
- 🐛 Report bugs via [GitHub Issues](https://github.com/Calisti-Daniele/moodle-local_aitutor/issues) / Segnala bug tramite GitHub Issues
