<?php

namespace GSS\Component\Hosting;

/**
 * Class SSHUtil
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class SSHUtil
{
    /**
     * @var array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private static $extensions = ['xml', 'vdf', 'cfg', 'con', 'conf', 'config', 'ini', 'gam', 'txt', 'log', 'smx', 'sp', 'db', 'lang', 'lua', 'props', 'properties', 'json', 'example', 'html', 'yml', 'yaml', 'csv'];

    /**
     * @param SSH    $ssh
     * @param string $sourceDir
     * @param string $targetDir
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public static function installTemplate(SSH $ssh, string $sourceDir, string $targetDir): void
    {
        $script = self::buildTemplate($sourceDir, $targetDir);

        $fileName = $targetDir . 'install.sh';
        $ssh->put($fileName, $script);
        $ssh->exec('chmod +x ' . $fileName . ' && cd ' . $targetDir . ' && bash ' . $fileName);
    }

    /**
     * @param SSH $ssh
     * @param int $userId
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public static function createLinuxUser(SSH $ssh, int $userId): void
    {
        $ssh->exec('useradd -m -u ' . (10000 + $userId) . ' user' . $userId);
        $ssh->exec('mkdir /home/user' . $userId . '/.ssh && cp /root/.ssh/authorized_keys /home/user' . $userId . '/.ssh && chown -R user' . $userId . ':user' . $userId . ' /home/user' . $userId . ' && chmod -R 700 /home/user' . $userId . '/ && chmod -R 600 /home/user' . $userId . '/.ssh/authorized_keys');
    }

    /**
     * @param string $sourceDir
     * @param string $targetDir
     *
     * @return string
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private static function buildTemplate(string $sourceDir, string $targetDir): string
    {
        $script = "PATTERN='(/valve|/overviews/|/scripts/|/media/|/particles/|/sound/|/hl2/|/overviews/|/resource/|/sprites/|gameinfo.txt|steam.inf|steam_appid.txt)'" . "\n";

        // create gameserver folder if not exisiting
        $script .= 'if [ ! -d "' . $targetDir . '" ]; then mkdir -p "' . $targetDir . '"; fi' . "\n\n";

        // cleanup old symlinks
        $script .= 'cd ' . $targetDir . "\n";
        $script .= 'SYMLINKFILES=(`find -type l`)' . "\n";
        $script .= 'for SYMFILE in ${SYMLINKFILES[@]}; do' . "\n";
        $script .= "\t" . 'rm $SYMFILE' . "\n";
        $script .= 'done' . "\n\n";

        $script .= 'cd ' . $sourceDir . "\n";
        $script .= 'FILEFOUND=(`find -mindepth 1 -type f \( -iname "*.' . \implode('" -or -iname "*.', self::$extensions) . '" \) | grep -v -E "$PATTERN"`)' . "\n";
        $script .= 'for FILTEREDFILES in ${FILEFOUND[@]}; do' . "\n";
        $script .= 'FOLDERNAME=`dirname "$FILTEREDFILES"`' . "\n";
        $script .= 'if ([[ `find "$FOLDERNAME" -maxdepth 0 -type d` ]] && [[ ! -d "' . $targetDir . '$FOLDERNAME" ]]); then mkdir -p "' . $targetDir . '$FOLDERNAME"; fi' . "\n";
        $script .= 'if [ -f "' . $targetDir . '$FILTEREDFILES" ]; then find "' . $targetDir . '$FILTEREDFILES" -maxdepth 1 -type l -delete; fi' . "\n";
        $script .= 'if [ ! -f "' . $targetDir . '$FILTEREDFILES" ]; then cp "' . $sourceDir . '$FILTEREDFILES" "' . $targetDir . '$FILTEREDFILES"; fi' . "\n";
        $script .= 'done' . "\n";
        $script .= 'cp -sr ' . $sourceDir . '* ' . $targetDir . ' > /dev/null 2>&1 ' . "\n";

        // GSS Files
        $script .= 'cd ' . $targetDir . "\n";
        $script .= 'rm -rf install.sh removed';

        return $script;
    }
}
