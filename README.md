# Data Estate Connector
v1.6.1
Data Estate Connecter WordPress plugin is a WordPress plugin used to connect with Data Estate. Anyone can install the plugin, but it won't work without a valid Data Estate API Key. 

## Installation
Download the release package from the release page. The latest release is [release (v1.6.1)](https://github.com/DataEstate/dataestate-connecter/archive/latest). Please note that you will need a Data Estate account to be able to use this plugin. After you've downloaded the plugin, unzip it in your WordPress plugin folder (wp-content/plugins) or go to you WordPress admin page -> plugins -> Add New and then upload plugin. 

## Configuration
After activation, some configurations are needed before the plugin can be used. Details of it can be seen in the settings page. Settings can be found under **Settings->DEC Config**

|Name	| Type	| Description	| Value/Validation	| Default |
|-------|-------|-------------|-------------------|---------|
|API URL |	text |	Data Estate API’s URL. The URL usually doesn’t need changing and can stay as the default value. Only change this when you’re switching to a separate environment (such as UAT, development or your own custom instance). | Valid uri includingprotocol.REQUIRED	| http://api.dataestate.net/v1/|
|API Key |	text |	A valid Data Estate API Key. If you wish to access ATDW’s Tourism Content,you will need to register for a Data Estate account with a valid ATDW API Key.|	REQUIRED	|
|End Point | text	| The main Estate endpoint to use. Usually this is going to be estates/data/ so it's best to just leave it untouched.	|REQUIRED	|estates/data/|
|Google Maps API key	|text	|A valid Google Map API key is needed, if you wish to use the Map widget.	|	| |

## Shortcodes
### Single listing (product)
| Short Code | Description | Parameters |
|------------|-------------|------------|
|dec-name | Display the listing's name ||
|dec-description | Display the listing's description | |
|dec-address | Display the listing's address. By default, this returns the street address as text.| <ul><li><strong>get</strong> - Return additional fields (i.e. get="suburb,state" will display <em>Runcorn, Queensland</em>).</li><li><strong>type</strong> - Display the type of address of the listing. Available values: PHYSICAL or POSTAL</li></ul>|
