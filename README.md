[![GPLv3 License](https://img.shields.io/badge/License-GPL%20v3-yellow.svg)](https://opensource.org/licenses/) ![GitHub Tag](https://img.shields.io/github/v/tag/vincentbitter/fcm-sportlink?label=Plugin)

# ⚽ Football Club Manager - Sportlink

**Automatically import Sportlink data into the Football Club Manager WordPress plugin.**

This plugin bridges the gap between Sportlink and [Football Club Manager](https://github.com/vincentbitter/football-club-manager), enabling seamless synchronization of teams, players, and matches directly into your WordPress site.

---

## 🚀 Features

- 🔄 Automatic import of KNVB data from Sportlink
- 👥 Update team rosters and player profiles  
- 🖼️ Download player photos from Sportlink
- 📅 Sync match schedules and results (coming soon!)
- 🏆 Import league standings (coming soon!)
- 🛠️ Easy integration with Football Club Manager
- 🧩 Manual import to apply changes faster and test the integration

---

## 📋 Requirements

- WordPress 5.0 or higher  
- PHP 7.4 or higher  
- [Football Club Manager](https://wordpress.org/plugins/football-club-manager/) plugin installed and activated

---

## 📦 Installation

You can install the plugin in one of two ways:

### Option 1: Download a Release

1. Visit the [Releases page](https://github.com/vincentbitter/fcm-sportlink/releases).
2. Download the latest `.zip` file.
3. In your WordPress dashboard, go to **Plugins → Add New → Upload Plugin**.
4. Upload the `.zip` file and activate the plugin.

### Option 2: Clone the Repository (development version)

1. Open your terminal and run:  
   ```
   git clone https://github.com/vincentbitter/fcm-sportlink.git
   ```
2. Upload the cloned folder to your WordPress `/wp-content/plugins/` directory.
3. Activate the plugin via the WordPress admin dashboard.

---

## 🔧 Configuration

After activation, navigate to **Settings → FCM Sportlink** in your WordPress dashboard. Enter the Sportlink Client ID of your football club and choose if you want to sync automatically.

---

## 📜 Changelog

See what’s new in each release on the [Changelog page](https://github.com/vincentbitter/fcm-sportlink/blob/main/CHANGELOG.md) or on the [Releases page](https://github.com/vincentbitter/fcm-sportlink/releases).

---

## ❓ FAQ

### Q: What is Sportlink, and why should I integrate it with Football Club Manager?
**A:** Sportlink is a platform used by many Dutch football clubs to manage match schedules, team data, and results. Integrating it with Football Club Manager allows you to automatically display up-to-date information on your WordPress site without manual input.

---

### Q: Do I need coding experience to use this plugin?
**A:** Not at all! Once installed and configured, the plugin handles the data import automatically. You just need access to your WordPress dashboard and Sportlink Client ID.

---

### Q: How often does the plugin sync data from Sportlink?
**A:** Data is synchronized hourly.

---

### Q: What kind of data is imported from Sportlink?
**A:** The plugin can import:
- Team rosters and player profiles
- Player photos
- Match schedules and results (coming soon!)
- League standings (coming soon!)

---

### Q: Can player photos still be uploaded in Wordpress, or will these be overridden?
**A:** The plugin contains a smart algorithm to check if the current player photo was manually uploaded in Wordpress. If so, it won't override it with the photos from Sportlink.

---

### Q: Can I manually trigger a sync?
**A:** Yes! There’s a “Sync Now” button in the plugin settings that lets you fetch the latest data on demand.

---

### Q: Is this plugin compatible with other WordPress themes or plugins?
**A:** It’s designed to work seamlessly with Football Club Manager. While it should play nicely with most themes, custom styling may be needed depending on your setup.

---

### Q: Where can I report bugs or request features?
**A:** Head over to the [Issues tab](https://github.com/vincentbitter/fcm-sportlink/issues) on GitHub to report bugs or suggest new features.

---

## 🤝 Contributing & Support

Found a bug or have a feature request?  
Please open an issue on [GitHub](https://github.com/vincentbitter/fcm-sportlink/issues).

Want to contribute? Fork the repo and submit a pull request — all help is welcome!

---

## 📄 License

This plugin is licensed under the [GNU GPL v3](https://www.gnu.org/licenses/gpl-3.0.en.html).