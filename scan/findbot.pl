#!/usr/bin/perl

my $access = '(\.htaccess)';
my $accesspat = '(RewriteRule)';

## Extensions scanned

my $scripts = '\.(php|html|php5|php\.|pl|cgi|bak|sh|txt|jpeg|jpg|png|gif|bmp|css)$';

## Patterns
my $scriptpat = '(die\(PHP_OS.chr\(49\).chr\(48\).chr\(43\).md5\(0987654321\)|die\(PHP_OS.chr\(49\).chr\(49\).chr\(43\).md5\(0987654321\)|social\.png|r57|c99|web shell|passthru|shell_exec|base64_decode|edoced_46esab|PHPShell|EHLO|MAIL FROM|RCPT TO|fsockopen|\$random_num\.qmail|getmxrr|\$_POST\[\'emaillist\'\]|if\(isset\(\$_POST\[\'action\'\]|BAMZ|shell_style|malsite|cgishell|Defaced|defaced|Defacer|defacer|hackmode|ini_restore|ini_get\("open_basedir"\)|runkit_function|rename_function|override_function|mail.add_x_header|\@ini_get\(\'disable_functions\'\)|open_basedir|openbasedir|\@ini_get\("safe_mode"|JIKO|fpassthru|passthru|hacker|Hacker|gmail.ru|fsockopen\(\$mx|\'mxs\.mail\.ru\'|yandex.ru|UYAP-CASTOL|KEROX|BIANG|FucKFilterCheckUnicodeEncoding|FucKFilterCheckURLEncoding|FucKFilterScanPOST|FucKFilterEngine|fake mailer|Fake mailer|Mass Mailer|MasS Mailer|ALMO5EAM|3QRAB|Own3d|eval\(\@\$_GET|TrYaG|Turbo Force|eval \( gzinflate|eval \(gzinflate|cgi shell|cgitelnet|\$_FILES\[file\]|\@copy\(\$_FILES|root\@|eval\(\(base64_decode|define\(\'SA_ROOT\'|cxjcxj|PCT4BA6ODSE|if\(isset\(\$s22\)|yb dekcah|dekcah|\@md5\(\$_POST|iskorpitx|\$__C|back connect|ccteam.ru|"passthru"|"shell_exec"|CHMOD_SHELL|EXIT_KERNEL_TO_NULL|original exploit|prepare_the_exploit|RUN_ROOTSHELL|ROOTSHELL|\@popen\(\$sendmail|\'HELO localhost\'|TELNET|Telnet|BACK-CONNECT|BACKDOOR|BACK-CONNECT BACKDOOR|AnonGhost|CGI-Telnet|webr00t|Ruby Back Connect|Connect Shell|require \'socket\'|HACKED|\@posix_getgrgid\(\@filegroup|\@posix_getpwuid\(\@fileowner|\&\#222\;\&\#199\;\&\#198\;\&\#227\;\&\#229\;|open_basedir|disable_functions|brasrer64r_rdrecordre|hacked|Hacked|\$sF\[4\]\.\$sF\[5\]\.\$sF\[9\]\.\$sF\[10\]\.|\$sF\="PCT4BA6ODSE_"|\$s21\=strtolower|6ODSE_"\;|Windows-1251|\@eval\(\$_POST\[|h4cker|Kur-SaD|\'Fil\'\.\'esM\'\.\'an\'|echo PHP_OS\.|\$testa != ""|\@PHP_OS|\$_POST\[\'veio\'\]|file_put_contents\(\'1\.txt\'|\$GLOBALS\["\%x61|\\\40\\\x65\\\166\\\x61\\\154\\\x28\\\163\\\x74\\\162\\\x5f\\\162\\\x65\\\160\\\x6c\\\141\\\x63\\\145|md5decrypter\.com|rednoize\.com|hashcracking\.info|milw0rm\.com|hashcrack\.com|function_exists\(\'shell_exec\'\)|Sh3ll Upl04d3r|Sh3ll Uploader|S F N S A W|\$\{\$\{"GLOBALS"\}|\$i59\="Euc\<v\#|\$contenttype \= \$_POST\[|eval\(base64|killall|1\.sh|\/usr\/bin\/uname -a|FilesMan|unserialize\(base64_decode|eval \( base64|eval \(base64|eval\(unescape|eval\(@gzinflate|gzinflate\(base64|str_rot13\(\@base64|str_rot13\(base64|gzinflate\(\@str_rot13|\/\.\*\/e|gzuncompress\(base64|substr\(\$c, \$a, \$b|\\\x47LOB|\\\x47LO\\\x42|\\\x47L\\\x4f\\\x42|\\\x47\\\x4c\\\x4f\\\x42|eval\("\?\>"\.base64_decode|\|imsU\||\!msiU|host\=base64|exif \= exif_|"\?Q\?|decrypt\(base64|Shell by|die\(PHP_OS|shell_exec\(base64_decode|\$_F\=|edoced_46esab|\$_D\=strrev|\]\)\)\;\}\}eval|\\\x65\\\x76\\\x61\\\x6c\\\x28|"e"\."va"\."l|\$so64 \=|sqlr00t|qx\{pwd\}|OOO0000O0|OOO000O00|OOO000000|\/\\\r\\\n\\\r\\\n|\$baseurl \= base64_decode|\$remoteurl\,\'wp-login\.php\'|\'http\:\/\/\'\.\$_SERVER\[\'SERVER_NAME\'\]|kkmvbziu|\$opt\("\/292\/e"|\$file\=\@\$_COOKIE\[\'|phpinfo\(\)\;die|return base64_decode\(|\@imap_open\(|\@imap_list\(|\$Q0QQQ\=0|\$GLOBALS\[\'I111\'\]|base64_decode\(\$GLOBALS|eval\(x\(|\@array\(\(string\)stripslashes|function rx\(\)| IRC |BOT IRC|\$bot_password|this bot|Web Shell|Web shell|getenv\(\'SERVER_SOFTWARE\'\)|file_exists\(\'\/tmp\/mb_send_mail\'\)|unlink\(\'\/tmp\/|imap_open\(\'\/etc\/|ini_set\(\'allow_url|\'_de\'\.\'code\'|\'base\'\.\(32\*2\)|NG689Skw|SultanHaikal|Mdn_newbie|Hacked by|PCT4BA6ODSE|preg_replace\(\'\/\.\*\/e"|preg_replace\(\'\/\.\*\/e\'|preg_replace\("\\\x2F\\\x2E\\\x2A\\\x2F\\\x65"|\\\x65\\\x76\\\x61\\\x6C\\\x28|\\\x67\\\x7A\\\x69\\\x6E\\\x66\\\x6C\\\x61\\\x74\\\x65\\\x28|\\\x62\\\x61\\\x73\\\x65\\\x36\\\x34\\\x5F\\\x64\\\x65\\\x63\\\x6F\\\x64\\\x65\\\x28|\\\x2F\\\x2E\\\x2A\\\x2F\\\x65|strrev\(\'edoced_46esab\'\)|PCT4BA6ODSE|N3tshell|Storm7Shell|Locus7Shell|function _542601441\(\$i\)|\$RA8A217F7E0C1915E2525CDED1A05F00E|\$qV\="stop_"|Pz48P3BocA0KDQoNCg0KDQo0ZigkMTN0aCA9P|passssword|pastebin|FilesGirl)';

my @defaultdirs = ('/tmp', '/usr/tmp', '/home', '/var/www');

my $MAXLINES = 40000;

my($strings, $md5sum, $file, %badhash, %white, %black);

&inithelpers;
&badhashes;

############ MD5SUM finbot.pl ###########
my $findbot = `$md5sum $0`;
	    chomp($findbot);
	    $findbot =~ s/\s.*//;
############ Listas Manuales #######
&whitelist;
&blacklist;
####################################

#my $executable = '^(sshd|cache|exim|sh|bash)$';

if ($ARGV[0] =~ /^-c/) {
    $patterns = '(social\.png)';
    $scripts = '\.(php)$';
    shift(@ARGV);
}

if ($ARGV[0] =~ /^-/) {
    my $l = join(',', @defaultdirs);
    print STDERR <<EOF;
usage: $0 [-c] [directories to scan...]

    If no directories specified, script uses:
$l
    If -c specified, searches just for one set of cryptphp
    markers.  May miss newer versions

EOF
    exit 0;
}

  

if (!scalar(@ARGV)) {
    push(@ARGV, @defaultdirs);
}

for my $dir (@ARGV) {
    &recursion($dir);
}

sub recursion {
    my ($dir) = @_;
    my (@list);
    if (!opendir(I, "$dir")) {
	return if $! =~ /no se encuentra el fichero/i;
	print STDERR "$dir: Permiso denegado: $!, pasando al siguiente\n";
	return;
    }
    @list = readdir(I);
    closedir(I);
    for my $mfile (@list) {
	next if $mfile =~ /^\.\.?$/;	# skip . and ..
	my $cf = $currentfile = "$dir/$mfile";

	$cf =~ s/'/'"'"'/g;	# hide single-quotes in filename
	$cf = "'$cf'";		# bury in single-quotes

################## LISTA BLANCA ####################
	if (-f $currentfile) {
	    my $checksum = `$md5sum $cf`;
	    chomp($checksum);
	    $checksum =~ s/\s.*//;
	    if ($white{$checksum}) {
		#print STDERR "$currentfile: LISTA BLANCA - PRUEBA\n";
		next;
	    }
}
################## LISTA NEGRA ###############################
	if (-f $currentfile) {
	    my $checksum = `$md5sum $cf`;
	    chomp($checksum);
	    $checksum =~ s/\s.*//;
	    if ($black{$checksum}) {
		print STDERR "$currentfile: Archivo en Lista Negra!\n";
		next;
	    }
}
######################################################

	if (-d $currentfile && ! -l $currentfile) {
	    &recursion($currentfile);	# don't scan symlinks
	    next;
	} 
	next if ! -f $currentfile;
	if ($mfile =~ /$scripts/) {
	    &scanfile($currentfile, $scriptpat);
	} elsif ($mfile =~ /$access/) {
	    &scanfile($currentfile, $accesspat);
	}

	# up to here it's fast.

	next if -s $currentfile > 1000000 || -s $currentfile < 2000;

#print STDERR "$currentfile\n";

	my $type = `$file $cf`;

	if ($type =~ /(ELF|\d\d-bit).*executable/ || $currentfile =~ /\.(exe|scr|com)$/) {
#print STDERR "cf: $cf\n";
	    my $checksum = `$md5sum $cf`;
	    chomp($checksum);
	    $checksum =~ s/\s.*//;
	    if ($badhash{$checksum}) {
		print STDERR "$currentfile: Malware detectado!\n";
		next;
	    }

	    my $strings = `$strings $cf`;
	    if ($strings =~ /\/usr\/bin\/perl/sm) {
		print STDERR "$currentfile: possible binario ofuscado\n";
		next;
	    }
	}
    }
}

sub scanfile {
    my ($currentfile, $patterns) = @_;
#print $currentfile, "\n";
    open(I, "<$currentfile") || next;
    my $linecount = 1;
    while(<I>) {
	chomp;
	if ($_ =~ /$patterns/) {
	    my $pat = $1;
	    my $string = $_;


## Wasn't printing the result correctly, so we gave up on this code.
#####################################################################################
#	    if ($string =~ /^(.*)$pat(.*)$/) {
#	      	$string = substr($1, length($1)-10, 10) .
#				      $pat .
#				      substr($2, 0, 10);
#	    }
#	    $string =~ s/^.*(.{,10}$pat.{,10}).*$/... $1 .../;
######################################################################################
	    print "$currentfile: Cadena sospechosa ($pat):\n $string\n\n";
	    last;
	}
	last if $linecount++ > $MAXLINES;
    }
    close(I);
}

sub inithelpers {
    if (-x '/usr/bin/md5sum') {
	$md5sum = '/usr/bin/md5sum';
    } elsif (-x '/sbin/md5') {
	$md5sum = '/sbin/md5 -q';
    }
    for my $x (('/bin', '/usr/bin')) {
	if (-x "$x/strings") {
	    $strings = "$x/strings";
	}
	if (-x "$x/file") {
	    $file = "$x/file";
	}
    }
    die "Can't find 'md5' checksumming tool - normally in Linux coretools package" if !$md5sum;
    die "Can't find 'strings' tool - normally in Linux bintools package" if !$strings;
    die "Can't find 'file' tool - normally in Linux 'file' package" if !$file;
}
################################################################### MD5 WINDOWS ###################################################################
sub badhashes {
    map { $badhash{$_} = 1; } ((
    	'f7536bb412d6c4573fd6fd819e1b07bb','0fdb34f48166dae57ff410d723efd3f7','396d1fb94d79b732f6ab2fa6c5f3ed39','fd3c01133946d59ace4fdb49dde93268',
		'ed3d4f60e85019c8d1b58791f2a45e46',
        ));
}
################################################################# fin md5 windows #################################################################

sub whitelist {
    map { $white{$_} = 1; } ((
		$findbot,
		'e9b6fdff81c90c09d4a28ce30f290fd0', #index.php
		############################################################## WORPRESS 4.1.1 ##############################################################
		'024b57d99bbe8b9e133316d1e98fc79d','02cb4a48791d540267d3f1bdc7b25a8f','03422c464e6e2323ffc27b1f9a13df67','03468b0f0ceb0ccce25de28ffed83efd',
		'0385e4a14de78c8b2a167f3e0aea197c','03870915e35d081758261432e7fe5f01','039a82ba14a35438ace23efba15fdf82','04051a3d44556bfa47c141f3dbbcf574',
		'0426b39754aa6bc766d89ea4c41bbd06','0432cd858b53685d9c4f2fa5371083bb','045d04e17422d99e338da75b9c749b7c','0486acb53c09a176858f71c23209abb5',
		'04e761d506e64836afab5d2550a3b8df','0500ec4ada37450a3ad97443f1d30ba2','052cd6502a05e7d3f17b3e76a5b15566','055aaf64dcc1456a3cd6b506649d72ca',
		'0573827ec7b9e08b5139c15a6ca23df4','057d4b0dc0761731460c3a78711242cf','058e2c44f7d9d41c50888fad6ba48577','05b8ea5fb11adb182563ddb989e091d1',
		'05c7517e06bb14b5eaa336c261b99b81','05eca53c435579fb51924fdfc4cc62cf','0659bf084f55a303f5922edc62bcfbf6','06d028f9b017f220016f9e9b932d135c',
		'06da247a78e2c415f8361bde93934d23','06f7aecb5bdfa28739eea0a498d15a81','0737683e3dda62486e3c11f7d2aace5a','076d1ae1dee8469a5dd5e543e2a87b3a',
		'07decee960d22fca5b6e94eaa647956e','07f370f6de7f7313f837fb55a34f22b7','081731ce01b46738e7adef8298ce8317','0871aadc0992a7c71d5b3558114438fb',
		'088645d63d59b9e2b876d25ecea8b591','08f8669f7453b17563a62e6bbb376137','09186e22f2a86dbde1dec5de41d00321','092478901ca7f090c5f85e442870725b',
		'094bfd76269c9fcc3c5cda8f05d05335','0981a8bd02ff25053fcfd8a562d75bd2','09ba9aa3753dfc6aaa747e46172d7f0c','09d1c3deb42e271f42e4cad6f22c4ba4',
		'09d91addb6b53479e68c645931d9658e','09e20150c7561d0330d7158f744abb4a','0a47c2f5734751d7728b2f28256cf222','0a4cb36fc6d4dea8cd435b347cf927b6',
		'0adfac2fc5cff2ab42e8f1e920b33e56','0b1b4da93b446a0352ef3fa9fa0f0881','0b7e1f9b2978e48c89f99c5befaf77f8','0c5569b8a2822ed086e78645b0c9f1de',
		'0c6799a3dec7fed821b2da980f3a3a8b','0cc1f173c956e57fbb16b56c9dfc3fb4','0cffd3c439bc636f35360cb19f51601e','0d28e73bb53ab02d4951460898c8ad8c',
		'0d4f8264edecbb041953ef3c360724a8','0d7a0005ba6a1fa29037258ddd1a2034','0deb5ea059c21f268c973b5ea0fcd21a','0e948ad7ea32644d4dcadc0f54fac1e3',
		'0ec99859384076f01ce50727d9bf18b3','0f097d201cb02ea92c179103eae598e7','0f3dc51f78d4b14bda30cb12cf062a7c','0f9b3cb2a6af84370b5576153951e237',
		'0fdeff4b8f0465f4b808da9d2ce2870e','1010da3327f8dcd8528c2bfd3ef0d962','104510e221fa08437aec008e633cdca7','105456f4e72329e3be147bf4fabf12b7',
		'1068fd448b38cff8171e9c0b42670ca5','109efa9312c00370894f7e2ba27e9c31','10aec0930bf49892f26ae12a2161d9f5','11064221ee789282972bcd7bc2d3dbba',
		'113914d2eedff268fb00dad3c3ac9175','113badd2d3bd1c9cec6ae9b51cf9d14a','1149b1a1442a543492ba931ff0b66ad5','115186cb516bf141e12b8b8c7f1c5c8d',
		'118f1189ffbb71e014402121b5456bc2','11c1107a87cc83ff1df4e588554e349a','11ef45641ee1574d581cd40331e316ec','11ef9de60bedb666594baca3e000e063',
		'11fb8132e10b3286a6c7719670a992a5','122e861f42eb6e01ce8d4b0f11fb735d','12692ddc4508038cd72704e4295e50fa','12a145e7dfcc3c63479cbdbf1d03140d',
		'12bf9e19374920de3146a64775f46a5e','13339a9e44c8c2124ddf8d5b60dda8d3','135321308e0cff17720381814fe670fe','13a4f38fa25bfc0d23a925a555154d8b',
		'1460ef31b2cffaef1cb012f531ae391f','14b2d04fdb85bc1f171cf3dfb2987dca','14b4c47ebc867b0918a2893eaccfb8ca','1529a8a7e5b39e3bec00542cca946c1f',
		'156ba5cf1bd1ed46270e3d459ee45a11','1581bb02286f54b4fb0cce52d2ef61c7','15a40c47867106bd6f45ac103d00d929','15c19112ce9637e3d6ddd43878e6d8e0',
		'15e634132196410e34dcf50820fcd942','1623db67f661ab9e16fe32a8c42ad17b','16523d5bf9efd8ca3b92e7631edfc513','169c038f805a493d8b5383670a02d89c',
		'16b6170794ca6d5f7fd29073d4a5477f','16d42ff617c4a616c3bd94ba103a4582','16ee4f9664fcd89b3504feaa2cad0ab1','172f499d40d4217bbf684cd552031acb',
		'17350ee7ff97f7afea2d824e5548a1eb','1755ee3900efb17a46a759c8572dc567','17c29039de6565917b07c6d99c9cecfe','17cbe5cbade2b4ec3d85be4ac9409add',
		'17e07d31d4b86e5d06058e11ae52f3f8','185bb6e6f980917f5b60c2364ed72891','186e51267fca5d20b230c72d9a8983ee','189217c8b067ef86add757922c2f75b4',
		'18ba5832006079f3bcbdeb4c38c92adf','18fd6764d8e84c235cf608de19a2ac7c','194efe4f47287621248a754cf99ae261','19e43c915ef2313cbdf1e1d4665e1226',
		'1a273db3c34f6afb3fed75417ca5e7b6','1a67509c005453afa0cd06dbe22937a7','1a7c5bfd9faf7f6cc77cd9b166062568','1aa57d225b7d9bb8bfa8500e0c2de029',
		'1ac435f167ec9c539cf405e139fc9a45','1ae13d535ba56678c4e08acf1989a3d5','1b074dccc9c4633fdbae077022442b88','1b1e9d1d12fcc51a151e7e0688bc695f',
		'1ba6cbb9e2a9d3deb348997492ed692e','1be4557714895c25adba8260adcc05ab','1be9174b160c7eb40e6cdce4031ae89e','1c2aa2f090782f3e37ff65878be943b5',
		'1c6d8b101c821641f983175221346112','1c8675dcd035cfb374f67bfcbf117a8c','1c986fe3039dbacf126de2f0dc644f6f','1ca4d589307f12d1cb208a33e539ed23',
		'1caa91f8b46fef4769aeb0730f6e296d','1cc8a2e6c0b5dd3176398d6400f0d9b8','1d4145177c30d6f02944c86d0b58f102','1d6822384a71090c74add106e4468581',
		'1d8eb51f53f479c82c6c660f7f40ad40','1d97b296d918482e1273c56fbff6a8e2','1dd3c7ed8699b740ebf4040b1304436d','1e123e96bd2a1ce2c0d3b305d153f1c3',
		'1e33290807fa8b2829ddb0347d0a9305','1e9b2359a41c9a8ec44cb1e002146f5a','1ecaee31ec029ded0e18f576958a5214','1ededbe306a41769751e39efa9bb53e1',
		'1ee31e93b5f8424bc82b4e1e477e09e0','1ef65c5e04ee40bdbc6d73cd15d578c0','1f670878467479584e672c34997e874a','1f7d847e2ad0617fe5d3b1fa8ea80195',
		'1f9bad25f80eefc9106f959180271eb5','1fd49547174da77755dfbe5a7559312b','1feb400a4e9983895a55cf7aa0078b07','1fffbeb699ec5bd9d4a738f78cc2b4a1',
		'2009fc37aa2c0950dfc7a62a213c67bd','20a8567ba70294295c115f7ed9e071b7','20c97a21993cf137ead9fdbecbc42aa8','214d14de7050941cf8602d369881c0f7',
		'21a79ede04fa5ee9017e6bdbdba5bfe9','21c1c848ca9d2ee8295afccb028e7449','22727a1ae25ad30d8dfce6cb130e58da','2297575ac6851b541ef9f8797d318ec2',
		'229cd304fee2141d2b8ac2be3002fbdd','22e12017d5533e1a3329de62e831c809','22e8745402ef9f879c4a2741118d8e3c','22f0a40a51c7512e0bc5c1f962b26fe2',
		'22f2a03358684885e9cfd7001df074a9','2391c6781dad9871a02cca33c1782f4b','23d5464450fa0c001ac9669747e7c6a7','23db5749e51d85105cb8d03fc81305c9',
		'23e11aff7e65f6998882dd4aac38fe05','242cf53d30c5475db6d02a6429724143','248f6caf6179ea6c4035b7eaec7edd6e','24a0227fbdd3acfd86ff03fc3fc6c8a4',
		'24daf495d09243fe7a3eb040d58ddcc7','252137d39d087bba598caf8cf94d2aec','256de214110572de8c5d34b8391163cf','259674b64ba2a4a2bda57b9c6154b498',
		'25c76e632c1ee2f2d25292b72acc17d4','25fe9d676b0c6a4062c025c6dfdc00d9','261fa850fde903097779eb27336f8c20','269e1c1bcb01aeb3fad829be7ed4aacd',
		'26b094140c6e6d5a6f3d7f4fdf7bc36f','26d5b7cd315570d025e09e11313d24e4','26dc8daaf0c47c4457b8bc2145f48634','270f0cd7341bce6c2afacf2682e7690e',
		'276f2225f21976d22740e82215494856','279a5b0bf93f8d81288dfbd117b6c77c','27c54160898b88b4dd86baa0c0618d1a','27cf3b654dc6a0d7270a746d665cab47',
		'280359f0abb9a9e144caad43644cbbc2','28a8a27aaaf51e9de260514ea7ddf409','28ed27bda94fbb5364fe1dbce0bac1c1','2952932c246bf9828429361643a8bb63',
		'2954ca0f19129336b65ed4a77b851f72','29669a79d39df6d72587a340d0ba7585','2984023919bf18888432e7459d70bf00','299972b5cdd1f1e0690dd95e4038bd87',
		'2a191e7168116418817388113bd57914','2a4e4af62d39344f6b6e689409d5c774','2a7a75a363b0f88f0b6d094a91ef65ea','2a8fc756a0859acaac1b9d20481979f5',
		'2b0b4e8d23d211fac38428825e524d6a','2b5153576d1eee4002fb7ed9e5831251','2c771d66d39708f0b0cac4c9922113d2','2ce3ee2783a33655a6bdfd9dc3cce174',
		'2cfd5c1b2e4288cef60faddbedeff8d3','2d12e5beadcc72ebafe916c3e191841c','2d5b92b61674c850bff00cecaf0864ec','2d6f96130cad55ca9310eed555a9555f',
		'2db6a9e6cd49d2429668ce40e0dee762','2dc6d49324d354247893235afb3cee63','2e154cb7d8df870459dfaf2c2a9b4b37','2e2deb24835a5f6c5259690d775a2324',
		'2e4bfe2b2bc4eecf362238b18b751b2b','2e8acb8dee99bfbcb61bd46c486a995d','2eec8f2c408c881c6715ce0f682f3707','2f04b950aa97a6642bdc88bbde7cf985',
		'2f37f221b8f77ae0e6bbde7b8d03fe43','2f61ab984c177275c71e34ff1a17c102','2f8cf087f0fd2648120054fde841f7cb','2fb1bc1a7f10d1dd54689a79b4cf53ac',
		'2fbf3d1bc2b37a65582ab97cd3eaa462','306b49c4726604b273860a46a7a69a96','311d098eca9a89370877334b1b8f992a','316dc6a88af5010df7bee09c481950e8',
		'318173b6ccb63ed80ba3d08563c3ff14','3185f27c8fa4123db79a1d6de055c9d7','3196e9b61f703909e139ce7e049a7ffd','3253906cffe4523bc05d0632af4c6af8',
		'3296d1fa20d292b002bba10490f1ba6e','32b3005887a4cb606fecc09c756605bb','3313dc2a4f322fd43349329cfde8191e','331e410c0d78ab864cc502f9fdac2633',
		'33afb6b43c87281e0482c8107b4957bb','34039211eb84b4c4ef8f040e7da8fb01','348071ed105ff0418b25964e771ba331','34dcd4a152172429fac7ebfb775a4ff5',
		'350af5af9077a62d67bae1f33a4f48fc','3534e4ec4ef74d6a8daafa225c11c918','3576ccddba4c3f91d3dd15747f9d2b14','358639f8d0a20c318054cb01c76ea4ea',
		'359b09c3ea4d3a11faf25e2ccc4d0b16','35cb8b380bbd1f2eaa723ac49ba5f3f0','35d63a4f5450fafdac209c305b496efa','361298a1f00ff6d56a51e0c3d2233194',
		'361d2b008f7fa66e15e45f48536cb5c4','368f987c644d70580097e48066c99082','36efb0b755b08835e34fc92747fa190c','376c100428103201b7109877a4afebae',
		'377e21e6dfe0008ef7c6d4fd2208770b','378a85bea60a9a4f044e94781f1a5a43','3790ff7e82d2d2dd9cb01106a33008d7','37a3cf8e3d5df4002c55d88834a294d0',
		'37b093aed28ac146f0e68ce5bdfc76e2','37b882b9e5354553532455391abbed99','37f26aba16eb341b2b1a6e36e5783e7b','38192cde34142cc7ecf558f58ef475f0',
		'38a8ed653819b9efc9e930f5c0b0986c','38ff692f79a3e57df9b9192a9e43b4ea','392e44e0a4438b7c538cd02705c39192','394bafc3cc4dfb3a0ee48c1f54669539',
		'395b6f2e9549c352d1fdda0d6b482c90','396651bcd43cc494c2f2239ee682e001','396d2dcb23efa97f63a1b2cc672188d9','397e3820b27a234330c95e05250f61ce',
		'39ca66318ef66201510aebcaad263210','39f67345a12faf1a3c53c9289fc59f86','3ab126e5adc2fe7bcb2a809183724891','3ac9b492e79e11f420cdc1e589030c37',
		'3b3c95f5fb16cfa309270c16992dc393','3b513d1231067dbe1c018150c800c90b','3b9a87754d6ddcad447c89778e93c544','3ba2182300e632340850329b7065da34',
		'3bcc587af2c7b01fc6fbc9c077050143','3c081f13cebd7133788e7778725c2032','3c524750546de1b3aab36ff60719aebb','3c6df510503404b2ac5be6ddba836293',
		'3cf06ffe0c240e6b1229a2b29e5f2d6c','3d2cb3f7baa628c9e51a326356e72038','3d655185b6170d7722edad3edbd4eb26','3d7013a46d09c74b0ee3d8af617412fb',
		'3db45ca97f2d1bccc5c7ec65abbf8b55','3e1abfa5fc227d5775166faa86842e48','3eb7c42237d19ed78921f0540aaa7df2','3f3c689e9ad6173e43bda54271d0ce70',
		'3f4eb897c1d10baf088c14dfc1808a22','3fa5188cc3cb000c2731e1c6641c4e58','3faf87337fb7cd137f149c287ee3f7a5','3fb5cd9ab947024d84585a0d693dcc12',
		'3fba1544df24f40dde5876c8c0aec461','4003b4b9b40eb568333e973f6905cece','405836dc36b41ce662dba3423eab616c','40f56f5a736da4effeb790cedb8a52f0',
		'4106f98f5e50d92365ae8de1bb31b17e','415a3787846bb6c2d745602c2afb73ac','41829b48e521eeac30d9d0209673b857','4193887cb9cb7f4d4d3000bdf303bf1e',
		'41a8a3186027727d08979f6288205147','41d8a5a34beeac3c0ad08709f6944c22','41f23e292a2fbedc21ecae2d04f29bba','41f746a4087bec7e9b0db4152759d169',
		'41fa39bcefcede21b93beb099cfa78d7','423326382961a7ef93c0ed3d487d6045','4271124f375797d87661ee9f98693463','429635d5e4118c5e9b001299609043fe',
		'42d8b8c0cf46b5d8a511e0ae48b88f75','42d8f3e2874f6523d36c403a502b2276','4302651322f5c66dbafcb6006f8273e3','43763096906608e1aaa769520f81fc4b',
		'4491e9d145c5610b78fe742fa95a5420','44c30028ec0e5983cc38a532be59c2a6','4502421f188ad9e38079741edc36e118','4530bd2071306dbbc61a592556b958a0',
		'453e7a3f8bbb417008f06d576c41d060','4587bd576acf7929e5360255156b900c','45975e2fcf0d428691a55a2394252f61','461149ddd29654b8f3d911f099f7b446',
		'46186b0162560fd40a1fb98c337b1ad6','462079214524104a870fdb0e7d1c9c00','4660617cfae6b1ec687fe800ea238cd5','46b6e1eab82ac9490a9a0f6746841574',
		'46cae1ca5cf883f4c91243447215ef11','47510d7560d22a974c8c0eec6e24bcbd','476d95fd191f1b59add31b389c033808','479122b088e353c76479d24bf10e7f27',
		'47af09bcc8af6e00917eff0eda8a28f3','48088a5167f4d99976ae72b40cc28d0f','4823c8667b23ca83b31bf9093647e5a2','48bb2baaf4353109f7c2665d96aa390b',
		'4922ec4f0a2ee9c6f5516ef3788af4da','4923271c0e79b5a346a440b9c0c76422','495e938d42ee492c80117e611b355247','49721f6a13d9cd7c43b744ed9742b5aa',
		'497445fdaced3a3f74ad9a252e2b95b6','4a121a5c6d72c6506750149b8e20ca56','4aa473c84c553159e9df569c73abfb56','4aa85a0c593cf769c7185911ae8462f8',
		'4b047923a816247ee4a32070b3e9c781','4b1542316956dbe7abc3db621865ed27','4bd55c320b9fd9d8127da94789693b4f','4c4b675114bc33b728f43f7c6d642f6b',
		'4cc2365d0450dedec30cec2e73a8a1d4','4cc64266f1b35a86c63cc1b2c42f7306','4cc86d1003c45134d6838f13e3885db1','4cf25341919f07dacd84ace1dc05251a',
		'4d42ec2d6781c14747e6b416f9e0a26d','4d432b17ea493ea3ad4c834457bfb08f','4d6ca4acefd6ee3f4c49019bdc19ef48','4d912846975670c9e2232a19ef7bb41b',
		'4e4658d148abeeb3d2366b951a8c65b4','4e4d93eda2a8c01c8ad639f18862fc25','4e59d34efb2da0b9a033596a85e4b1ef','4e85d4354373cc17b9099b130b121f12',
		'4effc1471f483ae67f4fcd3de04436e4','4f2215bdabcb2b4b32f3e62f1c913b12','4f4b04852e86713b9f7490e74ee8820a','4f526ee15d266497fbe7fe8e129c8d6e',
		'4f70432f595fade3c533070299719285','500723dfe627bcfe43936aa85bd6b851','512b871a2830e44259bc3ce3343afcd0','519bcf33a514d3da1ea78fe6738681e0',
		'519c5cad73fa9de0f11b7e78479e7599','51b755f1d00ecfe6c97c681b712a1f79','51cf442ecefc84f799b3daf33a0f82cf','521cb94b9501ca24bc495a31c66925d8',
		'52a7f011f362416f939a74215f7ebd12','52bb2ee462e7e414a77efdc7ebf52bcc','52d0c08fe45aca3f662e81c738f5e209','52d7accb82aef17fc2c3b4c58968dc48',
		'52dc838032e91584a03c7aa660c860c7','52f7daa26417fdbdc993a84748d5f181','536aed56921bffeaa9a36e10f3588174','53825e0f13ec9497ea097b6d9d9ebf77',
		'53c49f5294ae24d2796ad73b82db1954','53cf11a41f973a80570e0ecac5d3fe5e','53e25fcbec91e57c9127342e6f2736ee','541242a293805952a0e22234f09d6fa9',
		'5480b0fabf52c37eee8bfed6db52335a','548a71424a1978d624181c134b7779c4','54b12794da9f72eba7a1b7c742eef081','54d9e85b94c4e6368813852b9a81273c',
		'551a3e9dcf9854a5c5fcaafe716c6202','552cfb3a29ac01a0d88b0422c5517159','554dcd3ea78d13a3863fda5621edb74c','555b85af2c6ab97d6a41d89af715fc63',
		'55a6b7fb4b1b287497d3fc30910e97ce','55b5145ab862559d54083928033a2d44','564275703d82fa3ba9b199743730c06b','564c5df5e7f98ae88d546732251aeab2',
		'565968c6a4b133fae4b81cd3cc750994','5682d92e5f2542298a6ab34186891a61','5692871a8a7a1914ee0968ddf9923dec','56cc9ea201dc2f4b910e78bfacac9211',
		'570ce9d630599b95480576d6b71eae23','5725c7d0fb347f1c08df3690a58f3609','57597e8f4f92f87bf02b5f4c896b453b','576673884375879acedaef607da3f4e0',
		'57858bca396d7a20738cba617087c1e2','5798e3d2fb0180a9179b8bd7cf728eae','57fc7a21d1df2c4cbc4fb06dd43bdcc6','58091ac7639e586774b32895a7072e82',
		'585728972c1a992b5dd73cd9f355b1d3','586bc1619fe2aa146bef070f140fd386','5885552f89b4c18061da8c2e753c122e','5889a9c995d14bafefe1e5c886333930',
		'59443d279888d73242396855314096a3','594f599a5d8f91d07fc0f317117561a7','5973e0728909826d97bef5443b8ef72e','5a0e6d428fe58583f12326e7dc184db2',
		'5a1fd4519addb5a8bc1cba9bb2592d1d','5a8bfd37651305bdafbcf2cd51b0254b','5a8fe18c9848eb94ca40b5f17fd97719','5a98a86b5cb48c1506f338463259ce41',
		'5a9fd1c52b9007ab3d2afbaa6f2dcf64','5ac3c42cc86e745a5e36b67b4c70a134','5acea5e6f1956698dade085507fa91fa','5b122550eec0c22e85083aab44c80b18',
		'5b412ed4b30004589e4335a6d6deb97a','5b6bf92278874581371240737d6ca51c','5b76ef3302a6dfb5d6d7177ce5d13164','5bace01f99903e3cf56bb27bd2ec2891',
		'5c1371bcb4392968647852a9c9df5d6c','5c23255ad2d11db3f72c33b649f1389a','5c60d1674f9aa6f0b387aa7afa897698','5c611eaec684f19737c160f5f01a485f',
		'5ca61aeffbfa9bce1378169398c2d0e4','5cc3fe910d0b0c9c9acd316b5be59bcf','5cded14502700da5a6e8c0a00b8ce832','5ce371458c1a2148595f5f3daf7b5fc8',
		'5cfd7930cffa6412f75af26f2e689ed4','5d186224ebf4ddd0f1719c9ef4b80468','5d1b42b737158c277828afda18881573','5dbc79c3affe3cd17220d44ceb467c01',
		'5e414da7c33e52e8ee53ae20cc2c3bd8','5e6da10bd91d3bc1efdb71c105f8293c','5e921d0fa2cc66e0dd7a4f6ce8764cec','5eee1f967840b952b502c6993dbbfad3',
		'5ef6dfd8ec7550e071581d5c14658efc','5f222b295e30dd62dfe160985b95f495','5f3f3cb7c6d126548d7848dd5893434c','5f66a88c118be462a566029db50aa3a2',
		'5f81e56e3ac8ebf59ee135c253b835d8','60c3cc0b0f5810b027a067545c6429d8','60d7f9347ab7938af5cb10ef606453f1','60de57253ca9143a6f1e4aff10fc39d2',
		'611bc9de9e6a21a2a5e212e3662c310b','61b6b7b66df4e825ea7329b3b697572c','61bf8fc8c8c0c413d7209a085bdc607a','61c6df5b1e3a5cf6ebd565cb58f91e6e',
		'61e1a5c632622df1af36a7a51ac4f53f','61ea709a3314ba200a885e2465267aa2','6224959946185334967f4d9152e13b27','62abd50ca92eb2381a7c60e351f64c46',
		'633a3e818838090cdb8f691545977ece','633a6048309a6e23d4dedd9e62e65278','633d5348edb8b8d16a1e5c73483fa94a','63a5c16d7962e503172f0522c973d7cf',
		'63ab44313b088129ae932d8a423f3673','63adfdd74e61e01c62e1a1e41cd37f64','63bf101bd3d4f7564d3cf31822218d2e','63fd697c7d66f00cefc6430b8c53c92f',
		'6453fcf875718d91560f5faedce361f4','645bd284dca21a69ec02e6f74201fdbe','6468f9823d831d5b4b4d37d5c52c88ac','64d0b35ed52e9b2c23e5e392a7a430b2',
		'64fd6a9dd5366830074362d07d205018','6508983f1ee0b5b93005ef566698a0b5','659d8f1d3bfd32cf405f6591d0521e7c','65d871feccd57666ee19a3df4644a7e0',
		'65dcc85d3a75ff5776fbe3df0122b7e2','66256995400e51a5f931a11bc11e1e4e','66b227ca28f41f2e0615b04a390d5e04','66dad33222d97a59086c0ffa2f9cb683',
		'66ed7befbb2773566ed188e1d3b97cc4','66fb081d5b32491c726ddfe927213260','6705bda44f3acb5dc98fc1bcc65884db','672556d294d18deaeaf88e1bd4aa63a2',
		'67314499128fc5f9b92a78e2ac93bf89','67442c5615eba73d105c0715c6620850','67b3b8bbb2d4166c5da5346a306c3d9d','681466e5980a5b99d9baeded56c67d34',
		'6859d3a0a826e984df36d3098cfd874e','685fb2f73a372f615a6981a8cd1d2444','6882c09b189df57714ce0dbf7634d0b6','6883026cbd3e72ba5da36c57c60fc078',
		'688a1f872b0afb1ec82b4f1d8f74fc20','68d5bb134953c23217fdd36982679a0c','68e539081693b1ed4a42bf1c284004e6','68fcb351d6882f64a3e5e2f3d6fd00b3',
		'692f8e861bafa31fbf1b3805b4b0d7d3','69304ef4901478a94c0dfaba7fca595d','6978871856b8ff6629abcca584235417','698538b14fb9839aecd01d5e97c66316',
		'6a47fff5fee2f97bbf3eaf5e3b2482d3','6a67d324a8fd5c24c029d587b048d14d','6b2ad5537d4f37ab9202b59b07e7c412','6b79350bf46e0f692a4d1b2807ed0399',
		'6b8ccd8d31b47d8c367bc69afd38d127','6b93bea4404d74bc207a1dd86e7c85b3','6bbd804f795fa5a934f529a51a9886bf','6bf9e5219c34728a89e1e0a987e615d0',
		'6c2cd70b1c8972cf25b85e1e78a5251e','6c73fc31823a098f0fe2e76ef4b9779e','6cacc03d352d4b3ed70dd6a974fcedf5','6cb8bb7d4daad1989037e36a0cf08e01',
		'6cd30957943015d015fb81ee62a336d8','6d1b9dd6ae801128cc1f671378688d8e','6d6bc60cd42263c2ffd0b4b6523400c5','6dc7476f6b2484c728ed3cd733ac80bb',
		'6e130e18b6ce4b832b4de648255e67b2','6e16ff20d3e68692cf3b617b875f36f5','6e1c9ca18902b56c36af3e28246eeac7','6e6ae4efeeb4b153955047d63d1f9703',
		'6e6b731c6abaae85f3885a9cce1981d7','6e919c53d0473de1b505306e23567a86','6ed95dbdccbef53693a35626187514bb','6f0074f1f2d119430222bc3c0950ac2f',
		'6f2ff03edaa59c1a94be0874d08971ee','6fb2642d8eb51b75a796cb3e92e6ba7e','6fd6a0edced7b413d16c500d896d5e0f','7018d1c633ea2971bb8600d1b788493d',
		'7029eb89afd82d9845f711c93ca1cb47','703c659e4bf563a05c6338a1727e006c','7055c8aaaf4ef6e0a98ce6b37b654799','70cb8b8659e2e242f15a210dd15cd234',
		'70d7ff2b68aa36956b1c7fa6c0b44191','7107331d19f8f4e93e7a34a0c7b8da62','7134abae3a441877786af588682ade96','71386ae29d2e92d744cd0ffcfe901ff2',
		'716436fb3df0d29e6b37dd62d952676a','7198cf8d485e8ddcb2b3bb49a6d069da','719980f24cf02c0c5fa53b312fef45ac','71f7f7d3b91fd496dd32e5b00667316c',
		'720418167ced7d4e1633fb64ba3d390f','7233eef8935a8539e806bee9e03bcea5','729d8f054952860976ea132b2fc5bc0d','72e8395fd44d4039009c5396888fa6ba',
		'732ff0fd6e5d9d64b2c8c81f7ac83f01','7343d1524fd75b23a9879d5a6e0d6b61','73a10d6e0f9198feeff1a5ef7f377f7f','74413a2404aadd63114f75e93d4e54c3',
		'744a72eb50e73ff117c662558802e661','74d903049683e5bbea9ccb7544a42bca','751419260aa954499f7abaabaa882bbe','75512b97d93030e09e5c7f9f3528dbfd',
		'759747ef8d44c52fadcfa5c457f3f283','75ab41dc91cd7e4aaa5e74a5f9e6eeba','75c63560c640c4a6c31f5565dfb0e8a9','75f6390a4a93fd4c613751b7a9e78491',
		'76288aadd77830d2a6d2255c76f4423e','769f771a3f3fdb68e59c9c71b0a7baab','76b326f4d44222126fee21076595bef5','76c195e5f157603feb495fe3fffbc33d',
		'76d2bc6a05541f605a17f3f55562aad0','76e5349938f6ce9179931436de1c64a3','770521594b392db90017a2f710b6d98a','772c324882563e12911807f279faedda',
		'779ffc62e3ce872a4cabb2c35bbb14f9','781b0a7f92ace4c740bebd0ba2ec9cc5','7850d87d05e8fce2ef72aa1dd954dc63','7852de09ac59b5589ed4dbdda4e4dee6',
		'78f5e202fde4da61a50d49b27e747eb2','79087fabcb00132181650bd80666c085','79f17b4392e752cf7c6f03f5b1346e93','79fc9017a23a836f4d0f68f7764ca734',
		'7a0f64800cf38b2be8d3dc4540ec31dd','7a782b3c28c8d32904c8caffe5d764ad','7ab98773e6e430f718c89d9f5119804b','7adbf03fa2ee150196ec223a7470cbf0',
		'7aea8668c85e32b92e91df74d6e9b261','7af92b8e79eb872268168985e58f929b','7bb46b093fff210b48954e9f6dab59a8','7bce36bf2355513af7917c193e23ebd6',
		'7c0001dfae602cc375d4107060bc732f','7c0ddbafae617300272f05428d36a7d7','7c2a5f42a243f9db4a19713b94c79504','7c2fb4ffa453c9870793dd257109b9b3',
		'7c43f8346fe150eaf84c8c4c633e4e61','7c6b4a4b5466657ea321d65ddb0aa04a','7c72c3f369855562d96c77ece1c7db33','7cb492260f22ee53816d96be3868be6d',
		'7cf21db8661f9201a784f638f77d2b26','7d28cad92829b3d633a087b5f3b595af','7d40dba8b873c1f982abaa89494584f1','7dbe1ed65cece832d15f0c0831e2076d',
		'7def33aad959cd289d49ddf2a41f076d','7ea2c9c157c38edb40b1ce62d572d5b3','7f65dde79eb89e98aa8dbe67fa5febc2','7f890eff523aefdfb1350cd22436899c',
		'7f89275e639a73c0f9c4448bf933545c','7f96038b9537a8fe2a8887a4a4893737','7fa57ec00dda88dd6b5c2037ccb4d5cf','7fda76920124f03e88d1dfd93e03bf59',
		'7fefa473594650055a36b9e3062c9a91','804f9a460fa9e3646d83f915c51cd36a','8073a4c6da1bb33b877576665ef5eab5','807b2938032727aeeeaadc388c95f875',
		'80a3c71f73734d8e64568b208b5cfd19','80e901ee9b9656e4081ebd582bd53199','8193a7042647f3156229dd76122fff55','8208296792cb09895d3599c50a621eb6',
		'8240ce3d6dfd3ffaaa20d71c67b9e5dd','82d07f23593e578820b19fc9faad65a0','836657dba5cb229000024477660e9ddf','83c9c867334174183419731af252c28e',
		'83fefeea5786545e775f25941e482d13','8407d1561eaa9c70d6aa1b5a9b877f62','848c81a12056b270d872ed2e186ea016','84c9b262ae5a4b939e934bad57d8ee3a',
		'84dba0f9d4eb8476be216c22c8a4f32d','84e1281cbdd11ce65648ff74e56e303c','84fe73be3590184176a9e97780785a0d','8564e463d51205a7ba81fc3dbf47534c',
		'857396f2d8203aba1028fde0009c92d8','8604c201ca1b346289d82daf2b6c91f6','86206b2005066714126439b8f5354770','864bdb066f1a4b663a1d729ae4925a27',
		'8674614341f1bec5feb22eb7e75adfc0','86aedc9045771a6f2d79696a120cd203','86b05d8ef230b4774bc234979974f469','86da19a9b131b04a7fe12d38c592ed37',
		'8703c9091ff1fc8dea8abff0a0d8a23b','870511532b062a500c95ae81e1cf23d3','87a08ca86f25ee997a627ce4a88ec359','87bddde1890612b32a9a4672e5d26661',
		'87dcfbe97f902fa77cc4a9889c827afc','880ae56e35b150b4b2c7e9d94227e81e','883cc839bfb38083f72d41214bed2473','88453ad3ad48657438d549e51a7378a7',
		'8874e7d5218c5771b75f5e733a52cf25','887e6dfef35ca68da3aa656d2c5d1d06','88d81b84f0975f4653c6cd20f3f2fb20','890e3010051b5a36851147fa7ff82ec7',
		'8932f8433bb77dcfcc6a2fa57326d46f','8974f84c7d69db81f2642767c337b81a','89eab3c62d8a7e8d143ed648c593d3fd','8a3c425cdc87706f0e2f30643e278348',
		'8a457a7b9d43377c070b0fe91732ed95','8a6c5d01c0ae57b622ce0a20b6de3285','8a8d737615b5c982687aa82aca65f893','8a95dbfaa99809b0150687ae0cb45aed',
		'8acc18afc849d02b6fd4050074a93a9b','8bc5ca12fa38a607d5af2181311b7a5b','8bc6b46bc70c7c1918dce62c4fe3229c','8bd6795f68ac02eecf6b528514d315de',
		'8be2b9d1eeaaeb053236de49c0b3efcd','8bf00b23dafb248f022d8b21693e0418','8c94db3246f7e7de1c2b745fdae79383','8cc40e1213764ceac9d4d7734677a7f5',
		'8cccae9c1ebafdb83be602e4d44c6f0a','8cf921d9d924ff67426908bd83f95c6e','8d7de25287ecf81d4e0d04534fa900d0','8db7f2acb2c205b766167517ccce7f8a',
		'8dd947d29128c7ebe5f2ef13609b4944','8de0e9f175ea68179b81dddb71a010f7','8de88527f924b455fb6d14bb7805f25a','8e83bb1de3e018f0537bb32a8c9617ff',
		'8ed550bdfaadf21e1806b0ca8462bb2e','8f59128f2a27b489b0a974c0b6b21046','8fa847b3101bc6a314bad8945e882caf','8fb0729c541cbdc4609faf3f4ad02fc7',
		'8fb1da7028c385bb9d4203c9f6732362','8fecf12e8ea164ba98d8236b0fb56c0b','90cc20d1b2aafc23be64ff2511e35bb5','90cc2e0760fa019d3429b601550c6430',
		'90e237d5f01035b958feaf514ef27f7a','9107bc56fc8c43be8e4303d5ada0d7ae','91567c7d8859d2bdd1f432db060e16bd','9184e53f96bade3e7ae7cda9eddf7a26',
		'91b98b79c9ff9dd201f640427d437e44','91d3e12051462662b3af459053a2704e','928adcedcd52b828e51f9ec291655e01','93340ac2d020159c171ae87c9d0a941b',
		'9349f636c747a5e983020a1cb7213a44','9396dfe1c69c938eb17f564c4e5bab18','94010edbfd8e6ca589daa4b83bf53d0b','940171d1392bd8071122a905d12b9195',
		'9405e022d36cdf45f029d94518ce4103','9443eda189bbd9325d0c9c045d237c6a','9465e8ca431502608536fc6c46f9ad9e','94e2dbd3e3fa59d8d70ef51e6bf3e81e',
		'9516d78a691d897911111481376a1f3c','957349ba7cfc37f34ba6754dd351eb1b','963028fda7041c7043675e6581a4fefc','96467eb5ae18dfa22ea1c0fa3e74380e',
		'9653ca07faf08e9ccc493041020e6eeb','96592c6b3fad580ce04e12bc3047ef3b','96949ed7e435e3134e76a76c6c3a80d7','971c65ba2e8084ec5bea8a000a66c141',
		'972051f086017dcef17964622336840b','974e6873c511db3a7129bf36d1eef3f1','978976c7bbfab9219a6f0a8a66a4da6f','9798444b97233bb341b19f374f85c11c',
		'979d8e2e08aada49819556950ec48ff6','97a79e96a815b200139356055d752333','97ddcd95d500418cd2114974ff644812','98747c729c8e35d2d6781cc587d9d291',
		'98a5f09955e3d1e3e0b2185de9f643a6','99408cfe66225d657d6bfe59611e4237','9953522fbd4a1b02bbf635a92d76cd8f','996e56f18f3ac9cf89f347d8d42d236b',
		'997023f9cb16a3208d43e6547fc7d4cc','99994a6d47dd86a9f64d7a358acc926c','99ec00da8d914b4efd2098a3e44ebe2d','9a05b49740dfcdaf4516851b623606e4',
		'9a0a326d308c1e48a0f89bd3ce6e2760','9ba4b71a75f877bef76ec6bb31f71df2','9c849e036b6ef9e2d058ffc2c4db63d4','9d119a515eff37751a19f95d11c0802d',
		'9d5459d3c59d32b602732c0df56d83bf','9d8c132ccaf6caf1d752be0beeaf051a','9e64d4eb0b08db6b14f1fb498f393caa','9ebeb22df3728735042a4a37a1496611',
		'9ed9dabd94f3a18eb8574dc2bd394ed2','9f0edb97343ebde66b1c2268bb1fb252','9f38d89f0e227bebd5ba84ac75e10f5a','9f78248e9e0a64aa17f3062ce25099cb',
		'9f8b1a50cba5f06427ffe004043128c9','9fa7dcf54586c1d680cea67b449ffd88','9fc2dfc9bae61ff303e5e5ba96830b34','9ffdba2cff497d701684657e329871f5',
		'a01ebc7a7becb4597d71d379bcdab4be','a0392921ac5f6e9719346b219a7bf1d6','a063ba711c87450a4a1277de23557e62','a073b8a1ee9b2482017f3628da40a861',
		'a0d449084ea7eb23a14f0c5c2f8a7dea','a1065fb19f8c105077f9b4501055db34','a1223f017d52327b385cac03833f52ea','a12a3034ff4734469709265a83697d22',
		'a1405fefca1712637de0e267909deb50','a14cd5113bd0d57563c1a9b63cae05f8','a1c18227e6e93798c493aed96ee6cc84','a21d278e00ca7dccfe3a81d4e386afa9',
		'a2a4e95cdac0b8a2e42733ae29dea751','a3107b7ddafb2dad47ab68d596d8617d','a32d1bbc44057b7dd0d2776ba2826b7c','a33dbb0540ecc29cc6425b14100953d1',
		'a3fefb4998b3f534e144db4f235d0f03','a41ed6c0bec0675d8bc3784351830f1b','a4ae19a923b890f2dcf7e2d415fd1ad2','a4b28b10d15c63e0e9aacea727f2e954',
		'a529e3d3c2bb86671fb9cc1145cf70ee','a546790e216abdd9801795949fb6b40f','a56c7a563660776d5a421c730b8dcfd6','a5b05bbf28f294b02efd942a4e5ab806',
		'a61647fd50e892d148bf77fab886afd2','a6387e1ea321244c1515ae80a7ff8790','a68f53a66c93f4485ca9acafaffc81e6','a6ba6c12feeced4055521c60e2312a9f',
		'a6c65fa6ff738ef6c46a4e80a65f7aa0','a6f2b4869450341fd4a85de9e9f1c3d5','a706ead694231e74fd6750b1670580a5','a73af354a03241715d8698feea340b92',
		'a7a67d1de1a0330fc7769d384a6564cc','a7d8955a04266243fb256dffdf04ccc9','a7db710dda2290bcf8c582ba14741626','a87f03a14efd9b8531164dac272aa07c',
		'a92923fbd3361aef7afbad9b137d4cff','a94bb299c353b7e57c9f98900cea2f7d','a97de8b82ec8b15faf9b1d529d408527','a9e5e1a632f1b3b962e4c426d55acc72',
		'aa3ba38e52eb57e5866bb0ff6c88d397','ab2501410060ab363b090f83b79717ba','ab3c6775af462066bdb485407007a49b','ab54b0e9583b36100738905f0d3a531d',
		'ab6393ed31d603a7c3b3d723437726a4','ab8124597a697247ad94c05a4b6284d5','ab91aa87aa7912c72a73d3240c9c7552','abaac3dfd81fbf72e578f13451eae7d0',
		'abd3613571800fdcc891181d5f34f840','abf293984a875379b5bf6dc1bb84a40c','ac21ffd2a75ab94f1600dd550c804ba9','ac2fc222d96b3c291fbcc74b8420b180',
		'ac627e9017143d091eb11ab6cf1ee68b','ac70389da35fe92417c39c96a468bfb6','acbaa2dc9041363b7fa2601f0e4df4fe','acea8fff302212325749ca45805add61',
		'acf3c2a0019a9232d54d4414802491b2','ad14c4d4216d3c0dffedd6cfe9d84f34','ad8496325608fb75875c49f64c0b84fc','ad923bbd7a9caf098f594d0e912379c8',
		'adc1e7b0262d80e44f18a287c395cb7e','adf093b1ec7239c03865a87ae6d60160','adf3b5ecfe050b4e66e2a0d08e944444','ae482d2d7599d139c87c00fb9b87ebdd',
		'ae9accf100a4b9930639adff52d4dcc7','aeba08ad6b558736ea0aaf2beb2925b7','aec141ea70457e90d42dde854a175957','aed976443b6e97cdc2ecae23df466d29',
		'aef5bd3a2b515ab84f6e9fff6a7d4b15','af2ae1a60e2c4bdbec69fe6c87c63cad','af52cf6d60f2edba609939a70304e601','af658c8c63fe1a0a9536fa504b962e92',
		'afb8e2f834d79d6e4735f1e96adbef5f','afc8bbc65fcbd2b82a3e2c1ab41a216a','b0a3dde331637e27aa6476d476481871','b0d32c87f2ad8bc2455ebf6a60171027',
		'b10eadbf41e88b236ac764bd26e653f9','b1458f7c51adab170056fc1fc4c3a3ce','b15f854ebf7711ffb4c1cf2839ba77a3','b1784295b00d1ae595ce9164004ce4b1',
		'b1f5d3eba80a1f93e0253bc74991fbb1','b229462e6a542696fbf6bd4917c9074f','b234ee4d69f5fce4486a80fdaf4a4263','b2984729c3b6cdc07508b88b5c0a4d1e',
		'b29ac75d7a34a9cd14f1ab9e660fdfae','b2b6c3e336054070e8927a5e7965f3ce','b369c6d4df45a622294874f96a746fb5','b399e0462769d55610fa310b01bc1bf3',
		'b3cf8e531a9c15bb7952877a43a5cd64','b4011d935c0f4dcf0cffc0f99d6d9680','b4031fcf4f4279be864d4bd82f7fc46c','b4419d2f79449b65dfe7036ef91cd1e8',
		'b44ae880533b69eb02aa8bab81bbb2a8','b4788daaa2fc81887659a2b21d888c0b','b4853cda3c7b4c55371939381cecdb86','b4ba824311d86552ddc7fe7753ef8925',
		'b51e0884a4d3518fa2eed8a2e6248078','b525a42af908fafcf8cc07679ab4fabb','b569e29ff8fd482e0ee75e1494085621','b57f6b1959c5f4ed3eb05d4474480c2f',
		'b5993b66434a99eb5b6f8cdd716f19d0','b5d7ef251e5976c7b1ba219ed8a62609','b5e49ae7ac24d2ee9f018419992d78a3','b5e58770c599ea25fa79b4a746957166',
		'b61b25303be0f573a6b9446d5cbe3a5b','b6b227f568b0bf088db26b96d2b81dfc','b7642fa0b3ddf241b7b4ee9d62139d76','b7742e45b6adea3547a54e1af3fe761c',
		'b7a95205254b5b44d7da6c40feee0f71','b7ab545f5b406a17f6bb6adc5c3f6f75','b82bf5bd3e3ec1958addaa62e0bade3c','b82f191be2383003721f8b0d6fbaaea9',
		'b83e0a563c9f2ac6ad6ef8266200b627','b9423b96eb6160477fd4a2b7de890419','b96fcf90ef2ba1739b59ab7bf67815f2','b989a5bd84f6ebcbc1393ec003e6e991',
		'b9b12a3c8353c6228ba30c0ae482f773','b9e8958712aef29e09242cf6477ff217','b9fa17a9811195800079dda4b1262d03','b9fb9fd3206264ae2dfc758320e4fe2a',
		'ba2c7ce5a3d288dc391cd440bc0224b7','ba45a05ecc211e8cab75b4d529ff75f7','ba45bdbe5bfd2ec67435d5e1ad05006b','ba7ec8cc3f13d4f27f2e0adcaf64bb2a',
		'bad695605e6db04e400a546f667eb70b','bb0f3b9d51439e02ac4a4475637c1269','bb4e961fad45ec703a9a4183955a90d6','bb7a03107ec589885fc42df7bc839e0d',
		'bbdb5de2ffa1378d42a1e0c1e47704d6','bc3602ceb3e5a0f05391eb77f48e3155','bc86a0275e39e66d7d29d97ffb6a5f0c','bcbc1b8d22c46e76d9a39507376b81c5',
		'bce0d6883fce649068cd407fc04486b2','bd119f2c6e5eaca98303aedbd95d067f','bd260e7477d834b7d0890b9470638124','bd502c39f6ac66dd8fc14fc6c47d47c6',
		'bd5a25f23589652ca472d41fe1484f0c','bd7fbf68b954a9d50955cc808db7cb6a','bdb3226d2568b8c1edf8f453b1e872e6','bdbabcdcca426a4dadf6675bc4c4ebe9',
		'bde568d89cdad9b1616ca1ef77f134dc','bdfd846a4df83eae1de40983354c1b4f','be54224152f2b4ec6879ffdbba435129','bee24b1d73ea1c8b24e0409d7d02251d',
		'bf0102a3fcc88324d1d200444fa6b14b','bf0ab9d631a3b25394db5d04866fc1aa','bf55f3b46b05aa372a0bed97b848de9e','bf81c1679c53d8d88dd1ec15904a6ffa',
		'bf8fa9a1830d020a8191eb02c7f74b54','bfcab5090b1280bbe495dbead4d2281f','c12daa75772a539d80c0bfffae2db05d','c1b465666e5fe74a78d59a4175dbe5d9',
		'c2076270975f96875b8472e3242fbacd','c2599121955a307a446a26437006ec9e','c28f6757fb9b072ed4ec796a96321e67','c2bcc33ea5397b37fc77de3149618d9f',
		'c369daa6877b943e3cfd58f57229bd61','c381190bf97c9f1ee41c777e93b12351','c39c41ae64a1706ef9fde6c4bcb0092b','c3e76ed756c4056fa9249a944f667e37',
		'c410e4cf76a54e8135904fdbac85e114','c42bf814a237dc89970d715ae8516b13','c42cf6e03ce2816679c6ec4ad630efb1','c463c9c7fe769e018d496cda106d6697',
		'c4833959e93d9368a35712e52c57c650','c4a5f119c255c653e1ff74af2b021baf','c4bb80ae54e4f97d63efeb0b84b287a9','c5307758e7cda56b18b721eaad458db6',
		'c538e2bc0e866197db616c17841134d4','c54ced2e822b232f2ad8a5f34930386f','c6207e0b437e8e4d8f13dde9f8b5c93d','c6470ea1e544fbdcd77696fdf5263fc2',
		'c6b0f979b9e66fc338f4cb3853a5608a','c6cba3cc3536f8a8fe608a3d0a9f2f77','c71759615ba051c1e6f597fd726a9d11','c71cccaeb645b4e75e963aecff2f5fc6',
		'c7597052fe2b16db307d6bd14e7b8c6b','c7c0438ad3cd61c120be41b484ffd4c2','c7dce545dd7b39f2e154c7a69fe1b30f','c841c027f1139ce197b43a9e4ea65420',
		'c86bbf1c64c924f99fdc9f5637f0c08b','c86c43956ebcd706efb11b2ff4da13ef','c8768695026080313465cdbd0297b725','c87fca030240b6d7113f3adcfb04fee3',
		'c892d5a2788972e35cec718b258d034b','c8fd98f7fdd52d78bdadf74e620789fa','c93e26230e05d4fdd87288b47c60873f','c95fbbd85d14b7f107c9363983ba78ff',
		'c97811c969982d3ec60a885c16333372','c9ae6d908e595b6f14b7aa2ad86000d2','c9b5e40dbdd6d711893e41b0ccc978eb','c9de8c70c10f50016e9ceca3a6ec8753',
		'c9f94c2964fbe89af48d431e721ee4d6','ca00eaecff50d9777022a34c4b36dbd8','ca4167ce1c99086ae6f3155af8728d49','ca4d97e656a62052423e53195ff42412',
		'ca4ef58229b7d12a77bb60cc6ea231d2','ca6452f3f58302707a0943d219b46ea1','ca890daf69ca1fa4112ed913a3a5afcb','cb0d04227b569b1febb596ee87e5e5cd',
		'cb3a6ab2d4997f0a5b2ee27c057035e1','cb3deca0110cd39ea85a9a3b65d3162f','cbe05bb060c85e07882dc06ff751577a','cc2012e2099931cd5db064122a44cb47',
		'ccb51571a75637db08545caaf2ed9e73','cccb0acc830cee3e5a2626dc44f6b2e3','ccf9b0c4144f1cf7e88d69942aaf2f1b','cd06c80f176a3f825c63bd4b5be9176f',
		'cd6dc830eb45b3a5a96bbc936ff54846','cddd08b3b3ed1ab3b09c1670d22347f4','ce2268030dd2151b63cdf4ffc2f626ba','ce7d9c841a8885991858466919cfa3f8',
		'cea23664cbf4f6c9484411cbc651d983','cecde679c62dd50207d8d25ece1a4b89','cf2ae664186202bf6944c31378ec5af9','cf6895af2050eb5ae7c0badda2604566',
		'cfa0d94d00f7a8a147c3815dc819e114','d01299c558225fb73b93732ff22a1278','d01a4f87055ac0fce8a66739d80434ba','d05abbeef1686261b73111eaf7b6926e',
		'd05d804ff45e4b73a6423f9eb66f686f','d0af21973275bb0564f8d1525e0d325e','d0c2b6f7a042a93d0d7abb1e83336ac1','d11319108bbf778fc46cdafceab10c4e',
		'd1308631bdc9f52b56bec9fe3b333e7e','d131056b90b61a4ed8d82b883e148411','d1469cd4079832849dad2664ea765a40','d15836bf65cf0a5682f2736cb27d4a4c',
		'd15c29a18d9ffa8b9b4ae86c3c0cfa22','d16dd6c3f76ad777188cc5708adda0c3','d18645f20ee8feb00e3b2b8556b36b65','d1dbac5be76d39851ce74aa134cc1aea',
		'd2251eecafb78029854d1bc3723b36f4','d22775b2e32645907141f788c36d4e9d','d22d9fa5bb00ba0667080da846c4a1be','d2673bd2dd98e5359b733f57ee3c4778',
		'd297d375677765e21f50fd15ba671647','d309316c1767ca923c2216d7348c2194','d30ad028653d4eac285a1d4d06567bbd','d314bef52d4210718abf723a84c0db97',
		'd34bd7474420e22e7da463b44833a5f9','d3d28113c92faef3c774e0d3344e6753','d3dc80c2e8346b81e1665cdcebde07d2','d3e07a826a2bdf3d9942cc6db7cb6ae6',
		'd3f697243af1efb6adfc7b4e01569af7','d41d8cd98f00b204e9800998ecf8427e','d420d2bafa7a4370a74f45ad61d956ec','d4f04dc65a387ca9b8c0f22ca8c0ec8c',
		'd514a077891ea4247e178a72c9345424','d57d191db57b711ed109c70c6795f4fe','d5b0877aeb74b5af37bcbb0dd7bcfb08','d5cb3c35a5d3d2be8decccdb1da30640',
		'd6b91fc8628a0c0474ad58389a475815','d6c36cfae1c6382ef99ad13bd59269d6','d6c89442c360bd1e08da2e7d1527373a','d774bf15e2e23e3a7bbb9afa92f4f0b6',
		'd7be08b669651a63080cfe7b9004d330','d8063afab6c49396eda7302919b45502','d810b096023695b38bf682f20774af98','d8127b8df949bbf73cff3c248da7b07e',
		'd85fe3394e177674d028dcb6c0012e10','d86926a7511d1d5cd3a2f0a502e7b6a8','d8b12eb9998cde79acb9cc6ae3e647b5','d920b4fb1be2c2c780081d5b4b7de55a',
		'd9728fafce781cdd98dc0ddf225a83c3','d9d03549d79484672c29145aad594db3','da04c78e3fc2016e9ef44f6adce36541','da07151366f79da6116cf5cfc3cc477b',
		'da3c43fff6f29048bc2c4427a4cf3769','daa52e28bfd88f5fb5587f17e51a1325','dac11175ce0497c386f2ec1f2bb18983','db7a8d16b367e4c38591be6d7e979876',
		'db9217196313c95a59d43601da19c51d','dba9e2475e7005e0bf78f6e761602144','dbc125543514eec3558b1232b3da73bc','dbc3808473def00fce45fe564dc72dcb',
		'dc4bffe1d10093e4d92533a8d60cba07','dcaf4f687c6c523cf0e2d5515234faa5','dccbd26d4d7ae80f4d1472923b769e96','dcdfce879761fde6123beb64cecf2af2',
		'dd4e6dd268a70ce4c1c5143b1a4092dd','dd83030fdd725c148b2b7a4aded9da13','df9254c728a075b313345528aa68355b','dfb747b363e0d019c519b7a8bada6efc',
		'dfd490b6f383ea02a269031ff05e8896','e002e949cdedaee5491c1c2e65f823e6','e007ab6019e9dc6b74d699f3cc7c2c36','e048709f08faeac3e1fdd8fb27869f0e',
		'e05a32249aef5de74b70d26a29f0a8ae','e089545cd7fcde5c7cd70de3a70139e1','e0d91074b5f13f0dcd4a34bcc561c329','e10d8139230f1f8b64f35960098f8cff',
		'e10ed46fab9c2698e592308ef1bc1274','e13c077043f6407e90fa25ec16f67493','e1af633d59dcb5988cacff73b6dee9ff','e1d64d4023fe0facd55ee1600b50a15f',
		'e24b2585db1193b02e488fadf94556f6','e286921c96e71c281fedc8376ba46a7f','e29483a8ca26a0dd8b0d1146c6b0a6e9','e2cd66b3e286187e9d7e00d6436b6d36',
		'e2d4ab1a66c1ec7615c2e17c15c7e081','e2d6eecbd774af1e2bb1a16ec117286b','e2f1b66e798b73956910c7f2d8f52893','e30f3a7bab4930977f62f34ce519854b',
		'e353217d4555ab5c62b367be6889813d','e37135428b98f334acc2ea4406100a57','e3ceeb9466bd89ba5ee791130f9ca85f','e3f39d6761a2cdce12638d33ad6171bd',
		'e449e3da6dc51f85fc4c571179dd9348','e458220573a3d5635fb49c4339e69b93','e4fe4585bf1930564ff8d572a0a5eac2','e5012902a358fbb96031acdcf048d7ca',
		'e56e363591f8ecc4ca9ad7ec4cde9750','e56f81676f199db7bf937e69a64909fa','e5ad272a18821212bee3c3df2ae8780e','e5afd8e41d2ec22c19932b068cd90a71',
		'e6c4f2d8329d1f52ef01edeca5065010','e701d7f8a987efc7bc7ccaf6804e8065','e71c620fc51e33ea2520d4fc4c4e6a1d','e747dbf1a9971017ccd7d387cfd117d0',
		'e78dcab5e8dc6bfad93588602a065c8a','e7b81d1bdbf1983d647a5df4ae156684','e7e2a2ba96d62d94728d386bd251e854','e8432364c55cef3a4c23e3c3047478bd',
		'e8911ece15df42ca43991a48d5785687','e8ad927ed99f0cb598babf3c97c08db3','e904f47d20c072a81da5729e94332f0b','e95ca1122287c8b6889b126ac893b750',
		'e961dcaadc176f0bc6345a04429ee6c8','e976956cebb7ba13ce258a9d2cfa8dc3','e9c7d804e2116ea44c498a6ae95a3c55','e9e33df9da15a95356e6da0e56889fec',
		'ea090d716dd05e4024c29283f3c88d0d','ea75a0238e8b23f52fbce4787bf302c1','ea8b5134bb61623d400396f9a9926153','ea970459148fa47f3cfb54d8af8a55b4',
		'eabe0530c91e13b16f2c8603b4713bc5','eafbb1478981e337981d287474e240b8','eb06befa8886e0c1858aa214e88fa829','eb148c7d02db4c96c7bcc6a2d276c22e',
		'eb207a02d03e3196d9d14ad139327fb5','eb246badf364507c2f0edb0f8e38c77c','eb4cd2c841fb222e136b5e7b891aea12','eb51ac1312eb060b9bc638fef46f80ca',
		'ebcf371dc5ff2088a4fe411ee8681466','ebd45002b7b73af9394cff249cfc3d1e','ebdfa9a5981fc4adc6421b8c8f2fbdab','ec9afc2dadea9918952e3e46ae56cde7',
		'eca6c02ab3707a042e39f47b88a5e153','ed03381714c58f75507c8ce11afd8c50','ed5f4318d41d867af5241b53202f52dd','ed68bc26b640e7dcaf6e32ea3d3478ed',
		'ed7b18351eecc9ba8b2b65e634696e40','ee19cf426d3e6e397a5d891f08d19ae2','ee4e3d326c58ca68a585902c0d264cc0','ee82540f026662197f7003474fd92de2',
		'ef3ae9014525cf81187afaa61bca737e','ef4188cb0b60a72017f4c8a1e840ab1e','eff55df37f325c5aae2f02e4d913de95','f01017ca562067f4840eb2b6f99f2daf',
		'f05db54c63e36918479b6651930dcfe7','f074a2f1a20c9aee61f75af61e2c1994','f17e509b09d2e396f6a9b615ecc9d542','f1ad65716432a0a1da7591a5c2f10d04',
		'f1c0a034e4f112d60054fcdecc873fb2','f1d3e9e205b2c0fecfd16283630f1a2b','f1dde3183fed93d9d3d01a5e578acea8','f2197bc5fffd870815d914eccf767eb3',
		'f21b69e890edc8d5f558470d8e8eb0cf','f25e56e30af6382e3770be437493373a','f26af7294ee07fb9a0cb88c2a8697623','f26c411cb8f645e88cca4f3837a62428',
		'f28025115e77028c2c0d0e77c1fd8cce','f337b5d28421e7ad1792f194ca15d421','f3726450d7457d750a2f4d9441c7ee20','f38d04d3a3cf83c12435370fd77c997d',
		'f3a14bbd16d7ac05e3918e96da30eb8d','f3b51c46d7be402a753613d97c68cfd0','f3e41626ce9c510857c69760a49e1c77','f3f1049fff65b59b6ee73c8324a06d6b',
		'f41708257e8e6bc89bd375f82a8bc290','f4da63e5c9c6e64e67d9662965af69d8','f51210deb422c7944a836d249ca7d12f','f558c13bfd84f79de2779a5847b8c459',
		'f5854a487c21837903b3e03e5507cf52','f58daaa070dd4ea21bd6790f7ec36e22','f5e118653f892606682ee9c51d0aba99','f5ea4194a79c23e653b24d0c65032e5e',
		'f60079fec0e5d4d67e49ada336daaf92','f623cad23a3ce005fcd054d9e5adcaad','f66d1ebe4decdac8163979876b0023e7','f69d4a55b2a1168531535107ab843fb6',
		'f6c2d0436843a5aa1a7fb39eeb6561b1','f701b8ba60980dfc80c607c36d76be7f','f737a235afd9a7b480eadae934cad0d1','f7c99ee74014fe92541012303aaadc7d',
		'f7cc436db8ef131f0d0543bc729bf1b1','f81355bd60d4d566e12e98309d46cbbc','f81c48acf15e31cbba34028652b6c662','f82ef9ea2e66e725aca286df49f09644',
		'f8310884cb84447a9c709a1805cf55a1','f83c42723f64875427828b5a179c3058','f83f27fb1bfd20d64ba2ae3eaaf4b1ca','f858439905295bd705b09b2dba3418bd',
		'f88e1b95ff278a5b231f39380b211ed0','f896b108803edf9db8bb3c72cc9ca343','f89c2c8567ebbc002ef9a5169a166993','f953a1f80de38a3aa9b33b9035d8a638',
		'f9e33829b8faed7d7bbef843fb683255','f9ff4694933001933bdec2c133b2252d','fa2427134de0cb97513159b49c4f25fb','fa6a758e81f3a6a617671e36c812369f',
		'fa9f09957ed6d30d5f0fe550793c9fa1','fb20ee6486993251b2345d7f10679170','fb9aef43aa9619c54b5aa197cbb33fc0','fbbccef47012aa7351a046dde3e6c830',
		'fc5c053316212fafe8abc20529546a10','fc6243e6ea74f2ca62bffb849de3657f','fcb5d20b06e3fd14e30ff9e923992c85','fcb7bcd40abc0a6d003bfc0fcbabe67a',
		'fcf693677ea822e6d24af7b2e4a98e99','fcf78dfab422bd8cfcb95f716d7e0182','fd37f9ac6c2ce46977468a5113d4716c','fd425da3ec4295915254f73403f78d03',
		'fd5b4eb05706a2f05f707fe077ae1030','fd74d6e6ca6119da16849dd23d18fb42','fd7d0df7b16e00fd528ccf616e4124d3','fdaba653baf259db7cb3d7a4d76a2970',
		'fde9e44a8aae0e89bd527792b4779aca','fe1c9596ef42d5ec7290be176db3e10e','fe9cd0c018a40dfcf014959e653bbabb','fed09c9b6be237c0fb4ba5c0468bb7ee',
		'ff2db8dbf145ce47f31781eef33e764a','ff37a40c48d23ba4ecc09d9a98da1247','ff69a6fd14bf4770b36a8880bf53dda8','ff69de2a050adee35d5afcc8d310da68',
		'ffb1c191d52ecd52959a6decd9f82eb2','ffd34afb44098936fc2be7362680e0cc',
		############################################################ fin worpress 4.1.1 ############################################################
    ));
}


sub blacklist {
    map { $black{$_} = 1; } ((
		########################################################## Versiones de CryptoPHP ##########################################################
		'ffd91f505d56189819352093268216ad','3249b669bb11f49a76850660411720e2','20671fafa76b2d2f4ba0d2690e3e07dc','5b1d09f70dcfe7a3d687aaef136c18a1',
		'325fc9442ae66d6ad8e5e71bb1129894','1ed6cc30f83ac867114f911892a01a2d','b4764159901cbb6da443e789b775b928','29576640791ac19308d3cd36fb3ba17b',
		'b75c82e68870115b45f6892bd23e72cf','f5d6f783d39336ee30e17e1bc7f8c2ef','2640b3613223dbb3606d59aa8fc0465f','e27122ba785627fca79b4a19c8eea38b',
		'4c641297fe142aea3fd1117cf80c2c8b','3a2ca46ec07240b78097acc2965b352e','d3c9f64b8d1675f02aa833d83a5c6342','048a54b0f740991a763c040f7dd67d2b',
		############################################################## Fin CryptoPHP  ##############################################################
    ));
}



