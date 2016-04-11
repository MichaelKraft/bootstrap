# Bootstrap

A script to bootstrap a fresh Mac to fully configured. Current as of El Capitan.

## Included Software

- [homebrew](https://github.com/Homebrew/homebrew) - package manager
- [homebrew-cask](https://github.com/caskroom/homebrew-cask) - app installation

### Brews

* Git
* Git Extras
* Logstalgia
* Fish - Friendly Interactive SHell
* Qemu (Part of Vagrant/Xenserver)
* `md5sha1sum` (Part of Vagrant/Xenserver)
* Ruby	

### Casks

* Dropbox
* Expandrive
* Handbrake
* Macdown
* Minecraft
* Slack
* Smcfancontrol
* Sublime-text
* Owncloud Client
* Bitbar
* Virtualbox + Extensions

## Notes

- `--appdir=/Applications --fontdir=/Library/Fonts` hacks are in the script until `homebrew-cask` does this as its default behavior
- Google Chrome and Firefox interact with 1Password when linked, must be installed manually until cask apps are moved instead of linked

