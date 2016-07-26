<?php
	// mac configuration script
	$stats = Stats::getInstance();

	PrettyConsole::puts10('Configuring System');

	RootPassword::disable();

	SystemSetup::assert('Remote Login','remotelogin','On');
	SystemSetup::assert('Restart on Freeze','restartfreeze','On');

	// preferences
	Preference::assert('Natural Scrolling','NSGlobalDomain com.apple.swipescrolldirection',false,false);
	Preference::assert('Firewall','/Library/Preferences/com.apple.alf globalstate',1,true);
	Preference::assert('Trackpad Tap-To-Click','com.apple.driver.AppleBluetoothMultitouch.trackpad Clicking',false,false);
	Preference::assert('Mouse Tap-To-Click','NSGlobalDomain com.apple.mouse.tapBehavior',0,false);
	Preference::assert('Expand Save Panel','NSGlobalDomain NSNavPanelExpandedStateForSaveMode',true,false);
	Preference::assert('Save New Documents to Cloud','NSGlobalDomain NSDocumentSaveNewDocumentsToCloud', false, false);
	Preference::assert('Guest Account','/Library/Preferences/com.apple.loginwindow GuestEnabled',false,true);
	Preference::assert('Show Full Names on Login Screen','/Library/Preferences/com.apple.loginwindow SHOWFULLNAME',true,true);
	Preference::assert('Preferred View Style','com.apple.finder FXPreferredViewStyle','clmv',false);
	Preference::assert('Confirm Empty Trash','com.apple.finder WarnOnEmptyTrash',false,false);
	Preference::assert('Empty Trash Securely','com.apple.finder EmptyTrashSecurely',true,false);
	Preference::assert('Show All Files','com.apple.finder AppleShowAllFiles',false,false);
	Preference::assert('Show All Extensions','com.apple.finder AppleShowAllExtensions',true,false);
	Preference::assert('Show Hard Drives on Desktop','com.apple.finder ShowHardDrivesOnDesktop',false,false);
	Preference::assert('Prevent Write to network stores','com.apple.desktopservices DSDontWriteNetworkStores',true,false);
	Preference::assert('Skip Disc Image Verify','com.apple.frameworks.diskimages skip-verify',true,false);
	Preference::assert('Skip Disk Image Verify (Locked)','com.apple.frameworks.diskimages skip-verify-locked',true,false);
	Preference::assert('Skip Disk Image Verify (Remote)','com.apple.frameworks.diskimages skip-verify-remote',true,false);
	Preference::assert('Auto-Hide Dock','com.apple.dock autohide',false,false);
	Preference::assert('Dock Tilesize','com.apple.dock tilesize',64,false);
	Preference::assert('Dock Magnification','com.apple.dock magnification',true,false);
	Preference::assert('Dock Magnified Size','com.apple.dock largesize',128,false);
	Preference::assert('Arrange Spaces By Most Recently Used Spaces','com.apple.dock mru-spaces',false,false);
	Preference::assert('Screensaver Password Delay','com.apple.screensaver askForPasswordDelay',5,false);
	Preference::assert('Default Terminal Window','com.Apple.Terminal "Default Window Settings"','Pro',false);
	Preference::assert('Startup Terminal Window','com.Apple.Terminal "Startup Window Settings"','Pro',false);
	Preference::assert('Show Battery Percentage','com.apple.menuextra.battery ShowPercent',true,false);
	Preference::assert('Flash Data Separators','com.apple.menuextra.clock FlashDateSeparators',false,false);
	Preference::assert('Menubar Clock Format','com.apple.menuextra.clock DateFormat','EEE MMM d  h:mm',false);
	Preference::assert('Analog Clock','com.apple.menuextra.clock IsAnalog',false,false);
	Preference::assert('Minimize Animation','com.apple.dock mineffect','scale',false);
	echo "\n";

	$checked = $stats->checked;
	$changed = $stats->changed;
	PrettyConsole::puts254("Checked $checked Preferences: $changed Modified.");

	if($changed > 0)
	{
		PrettyConsole::puts226('Restarting Finder...');
		exec('sudo killall Finder');
		PrettyConsole::puts226('Restarting Dock...');
		exec('sudo killall Dock');
		PrettyConsole::puts226('Restarting SystemUIServer...');
		exec('sudo killall SystemUIServer');
	}

	PrettyConsole::puts47('Configuration complete!');
	echo "\n";

	// brew
	Brew::install();
	Brew::packages(array(
		'git',
		'git-extras',
		'logstalgia',
		'fish',
		'qemu',
		'md5sha1sum',
		'ruby',
		'brightness'
	));
	Brew::casks(array(
		'dropbox',
		'expandrive',
		'handbrake',
		'macdown',
		'minecraft',
		'slack',
		'smcfancontrol',
		'sublime-text',
		'owncloud',
		'bitbar',
		'virtualbox',
		'virtualbox-extension-pack'
	));
	Gems::install(array(
		'synx'
	));

	// classes, functions, etc

	class RootPassword
	{
		static function disable()
		{
			$USER = $_SERVER["PHP_AUTH_USER"];
			$test = exec("sudo grep \"$USER.*NOPASSWD\" /etc/sudoers");
			if(strlen($test) < 4)
			{
				PrettyConsole::puts226("Disabling root password...");
				exec("echo \"$USER ALL=(ALL) NOPASSWD: ALL\" | sudo tee -a /etc/sudoers > /dev/null`");
			}
			else
			{
				PrettyConsole::puts254("Root password is disabled.");
			}
		}
	}

	class Brew
	{
		static function install()
		{
			PrettyConsole::puts251("Repairing permissions...");
			$directories = array(
					'/usr/local',
					'/Library/Caches/Homebrew',
					'/opt/homebrew-cask'
				);
			foreach ($directories as $directory) {
				exec("sudo chgrp -R admin $directory");
				exec("sudo chmod -R g+w $directory");
				PrettyConsole::puts47("Repaired $directory");
			}

			if(!file_exists('/usr/local/bin/brew'))
			{
				PrettyConsole::puts226("Installing Homebrew...");
				exec('ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)" < /dev/null');
			}
			else
			{
				PrettyConsole::puts226("Updating Homebrew...");
				exec('brew update');
				exec('brew upgrade');
			}
		}
		static function packages($brews)
		{
			foreach ($brews as $brew) {
				$installed = (strpos(exec("sudo brew ls --versions $brew"), $brew) !== false);
				if($installed)
				{
					PrettyConsole::puts254("$brew installed.");
				}
				else
				{
					PrettyConsole::puts226("Installing $brew...");
					exec("brew install $brew");
				}
			}
		}
		static function casks($casks)
		{
			$dirs = '--appdir=/Applications --fontdir=/Library/Fonts'; 
			foreach ($casks as $cask) {
				$output = exec("sudo brew cask ls $dirs $cask 2>&1");
				$installed = !(strpos($output, 'nothing to list') !== false);

				if($installed)
				{
					PrettyConsole::puts254("$cask installed.");
				} else {
					PrettyConsole::puts226("Installing $cask...");
					exec("sudo brew cask install --force $dirs $cask");
				}
			}
		}
	}

	class Gems
	{
		static function install($gems)
		{
			foreach ($gems as $gem) 
			{
				$test = exec("sudo gem list | grep \"$gem\"");
				if(strpos($test, $gem) !== false)
				{
					PrettyConsole::puts254("$gem installed.");
				} else {
					PrettyConsole::puts226("Installing $gem...");
					exec("sudo gem install $gem");
				}
			}
		}
	}

	class SystemSetup
	{
		static function assert($label,$key,$value)
		{
			$stats = Stats::getInstance();
			$stats->checked++;

			$command = "sudo systemsetup -get$key";
			$test = substr(exec($command), (strlen($value) * -1));

			if($test == $value)
			{
				PrettyConsole::puts251("$label is $value, not modified.");
			}
			else
			{
				$stats->changed++;
				exec("sudo systemsetup -set$key $value");
				PrettyConsole::puts11("$label is now set to $value.");
			}
		}
	}

	class Preference
	{
		static function assert($label,$key,$value,$elevated)
		{
			$stats = Stats::getInstance();
			$stats->checked++;

			if($elevated)
			{
				$currentValue = exec("sudo defaults read $key");
			} else {
				$currentValue = exec("defaults read $key");
			}

			if(count($currentValue) > 0 && $value == $currentValue) {
				if(is_bool($value))
				{
					if($value)
						PrettyConsole::puts251("$label is enabled, not modified.");
					else
						PrettyConsole::puts251("$label is disabled, not modified.");
				} else {
					PrettyConsole::puts251("$label is $value, not modified.");
				}
			} else {
				$stats->changed++;
				if(is_bool($value)){
					if($value) {
						$command = "defaults write $key -bool true";
					} else {
						$command = "defaults write $key -bool false";
					}
				} elseif (is_integer($value)) {
					$command = "defaults write $key -integer $value";
				} else {
					$command = "defaults write $key -string \"$value\"";
				}
				if($elevated)
				{
					$command = 'sudo ' . $command;
				}
				exec($command);
				if(is_bool($value)) {
					if($value) {
						PrettyConsole::puts10("$label is now enabled.");
					} else {
						PrettyConsole::puts9("$label is now disabled.");
					}
				} else {
					PrettyConsole::puts11("$label is now set to $value.");
				}
			}
		}
	}

	class Stats
	{
		private static $instance;
	    
	    public static function getInstance() 
	    {
	        if (null === static::$instance) {
	            static::$instance = new static();
	        }
	        return static::$instance;
	    }

		public $checked;
		public $changed;

		protected function __construct()
		{
			$this->checked = 0;
			$this->changed = 0;
		}
	}

	// Pretty Console for PHP
	// By Greg Schoen
	class PrettyConsole
	{
		static function output($string, $foreground=255, $background=0)
		{
			return "\033[38;5;{$foreground}m\033[48;5;{$background}m{$string}\033[0m";
		}
		static function __callStatic($function, $arguments = array())
		{
			preg_match("/^(?P<method>echo|return|puts)(?P<fore>\d+)?(on(?P<back>\d+)$)?/",$function,$match);
			if(!isset($match['method']))
				return;
			extract($match);
			$back = (isset($back) && strlen($back)!=0) ? $back : 0;
			$fore = (isset($fore) && strlen($fore)!=0) ? $fore : 255;
			$string = $arguments[0];
			if($method=="echo")
				echo self::output($string,$fore,$back);
			else if($method=="return")
				return self::output($string,$fore,$back);
			else if($method=="puts")
				echo self::output($string,$fore,$back) . "\n";
		}
	}
