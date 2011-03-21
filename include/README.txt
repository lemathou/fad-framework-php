Folder /include

Contains required informations for the application
Mosts of those files are only access functions, which load the asociated
class definition on the first call (from cache if possible).

--

Files :

header.inc.php : Loads the complete list of required files.

class_autoload.inc.php : Autoloading of most classes

lang.inc.php : Languages
security.inc.php : Security
login.inc.php : Login and accounts

db.inc.php : Database access function
modules.inc.php : Modules access function
permission.inc.php : Permission access function
data.inc.php : Datatypes access function
library.inc.php : Libraries access function
datamodel.inc.php : Datamodel access function
pagemodel.inc.php : Pagemodel acces function
page.inc.php : Page acces function
template.inc.php : Templates acces function
menu.inc.php : Menus access function
globals.inc.php : Global parameters acces function

exceptions.inc.php : Exception acces function
error.inc.php : Error acces function
gentime.inc.php : Gentime access function
data_controller.inc.php : Main controller for data
