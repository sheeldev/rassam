var commonPasswords = new Array("123456","password","12345678","1234","pussy","12345","dragon","qwerty","696969","mustang","letmein","baseball","master","michael","football","shadow","monkey","abc123","pass","6969","jordan","harley","ranger","iwantu","jennifer","hunter","2000","test","batman","trustno1","thomas","tigger","robert","access","love","buster","1234567","soccer","hockey","killer","george","sexy","andrew","charlie","superman","asshole","dallas","jessica","panties","pepper","1111","austin","william","daniel","golfer","summer","heather","hammer","yankees","joshua","maggie","biteme","enter","ashley","thunder","cowboy","silver","richard","orange","merlin","michelle","corvette","bigdog","cheese","matthew","121212","patrick","martin","freedom","ginger","blowjob","nicole","sparky","yellow","camaro","secret","dick","falcon","taylor","111111","131313","123123","bitch","hello","scooter","please","","porsche","guitar","chelsea","black","diamond","nascar","jackson","cameron","654321","computer","amanda","wizard","xxxxxxxx","money","phoenix","mickey","bailey","knight","iceman","tigers","purple","andrea","horny","dakota","aaaaaa","player","sunshine","morgan","starwars","boomer","cowboys","edward","charles","girls","booboo","coffee","xxxxxx","bulldog","ncc1701","rabbit","peanut","john","johnny","gandalf","spanky","winter","brandy","compaq","carlos","tennis","james","mike","brandon","fender","anthony","blowme","ferrari","cookie","chicken","maverick","chicago","joseph","diablo","sexsex","hardcore","666666","willie","welcome","chris","panther","yamaha","justin","banana","driver","marine","angels","fishing","david","maddog","hooters","wilson","butthead","dennis","captain","bigdick","chester","smokey","xavier","steven","viking","snoopy","blue","eagles","winner","samantha","house","miller","flower","jack","firebird","butter","united","turtle","steelers","tiffany","zxcvbn","tomcat","golf","bond007","bear","tiger","doctor","gateway","gators","angel","junior","thx1138","porno","badboy","debbie","spider","melissa","booger","1212","flyers","fish","porn","matrix","teens","scooby","jason","walter","cumshot","boston","braves","yankee","lover","barney","victor","tucker","princess","mercedes","5150","doggie","zzzzzz","gunner","horney","bubba","2112","fred","johnson","xxxxx","tits","member","boobs","donald","bigdaddy","bronco","penis","voyager","rangers","birdie","trouble","white","topgun","bigtits","bitches","green","super","qazwsx","magic","lakers","rachel","slayer","scott","2222","asdf","video","london","7777","marlboro","srinivas","internet","action","carter","jasper","monster","teresa","jeremy","11111111","bill","crystal","peter","pussies","cock","beer","rocket","theman","oliver","prince","beach","amateur","7777777","muffin","redsox","star","testing","shannon","murphy","frank","hannah","dave","eagle1","11111","mother","nathan","raiders","steve","forever","angela","viper","ou812","jake","lovers","suckit","gregory","buddy","whatever","young","nicholas","lucky","helpme","jackie","monica","midnight","college","baby","brian","mark","startrek","sierra","leather","232323","4444","beavis","bigcock","happy","sophie","ladies","naughty","giants","booty","blonde","golden","0","fire","sandra","pookie","packers","einstein","dolphins","0","chevy","winston","warrior","sammy","slut","8675309","zxcvbnm","nipples","power","victoria","asdfgh","vagina","toyota","travis","hotdog","paris","rock","xxxx","extreme","redskins","erotic","dirty","ford","freddy","arsenal","access14","wolf","nipple","iloveyou","alex","florida","eric","legend","movie","success","rosebud","jaguar","great","cool","cooper","1313","scorpio","mountain","madison","987654","brazil","lauren","japan","naked","squirt","stars","apple","alexis","aaaa","bonnie","peaches","jasmine","kevin","matt","qwertyui","danielle","beaver","4321","4128","runner","swimming","dolphin","gordon","casper","stupid","shit","saturn","gemini","apples","august","3333","canada","blazer","cumming","hunting","kitty","rainbow","112233","arthur","cream","calvin","shaved","surfer","samson","kelly","paul","mine","king","racing","5555","eagle","hentai","newyork","little","redwings","smith","sticky","cocacola","animal","broncos","private","skippy","marvin","blondes","enjoy","girl","apollo","parker","qwert","time","sydney","women","voodoo","magnum","juice","abgrtyu","777777","dreams","maxwell","music","rush2112","russia","scorpion","rebecca","tester","mistress","phantom","billy","6666","albert");
var numbers = "0123456789";
var lowercase = "abcdefghijklmnopqrstuvwxyz";
var uppercase = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
var punctuation = "!.@$L#*()%~<>{}[]";
function checkPassword(password)
{
		fetch_js_object("pwd_info").className = 'color-grey';

        var combinations = 0;
        if (contains(password, numbers) > 0)
        {
                combinations += 2;
        }
        if (contains(password, lowercase) > 0)
        {
                combinations += 3;
        }
        if (contains(password, uppercase) > 0)
        {
                combinations += 3;
        }
        if (contains(password, punctuation) > 0)
        {
                combinations += punctuation.length;
        }
        var totalCombinations = Math.pow(combinations, password.length);
        if (isCommonPassword(password))
        {
                totalCombinations = 95000; // about the size of the dictionary
        }
        var timeInSeconds = (totalCombinations / 200) / 2;
        var timeInDays = timeInSeconds / 86400
        var lifetime = 365;
        var percentage = timeInDays / lifetime;
        var friendlyPercentage = cap(Math.round(percentage * 100), 100);
        if (totalCombinations != 75000 && friendlyPercentage < (password.length * 2))
        {
                friendlyPercentage += password.length * 2;
        }
        
        if (percentage > 1 && password != '')
        { // strong password
		fetch_js_object("pwd_info").innerHTML = phrase['_strong'];
		fetch_js_object("pwd_info").className = 'color-green';
                return;
        }
        if (percentage > 0.3 && password != '')
        { // reasonable password
		fetch_js_object("pwd_info").innerHTML = phrase['_medium'];
		fetch_js_object("pwd_info").className = 'color-orange';
                return;
        }
        if (percentage > 0.10 && password != '')
        { // weak password
		fetch_js_object("pwd_info").innerHTML = phrase['_weak'];
		fetch_js_object("pwd_info").className = 'color-red';
		
                return;
        }
        if (percentage <= 0.10 && password != '')
        { // useless password!
		fetch_js_object("pwd_info").innerHTML = phrase['_weak'];
		fetch_js_object("pwd_info").className = 'color-red';
		
                return;
        }
}
function cap(number, max)
{
        if (number > max)
        {
                return max;
        }
        else
        {
                return number;
        }
}
function isCommonPassword(password)
{
        for (i = 0; i < commonPasswords.length; i++)
        {
                var commonPassword = commonPasswords[i];
                if (password == commonPassword)
                {
                        return true;
                }
        }
        return false;
}
function contains(password, validChars)
{
        count = 0;
        for (i = 0; i < password.length; i++)
        {
                var char1 = password.charAt(i);
                if (validChars.indexOf(char1) > -1)
                {
                        count++;
                }
        }
        return count;
}
