<?php
/**
 * Created by JetBrains PhpStorm.
 * User: walter
 * Date: 11.12.12
 * Time: 07:48
 * To change this template use File | Settings | File Templates.
 */


/**
 * Alle Netzwerk Laufwerke mit Pfad speichern
**/
$netDrive = null;
$WshNetwork = new COM("WScript.Network");
$ar = $WshNetwork->EnumNetworkDrives();
for($i=0;$i < $ar->Count(); $i+=2)
{
	$netDrive[$ar->Item($i)] = $ar->Item($i+1);
	$netDrive[strtolower($ar->Item($i))] = $ar->Item($i+1);
}

$WshNetwork	= null;

/**
 * Netzwerk Laufwerk in UNC Pfad umwandeln
 *
 */


/**
 * Netzwerk Laufwerk in UNC Pfad umwandeln mit UTF8 dekodierung
 * weil Windows File Funktionen mit UTF8 nicht funktionieren
 *
 * @param string    $filepath
 * @param bool      $utf8_decode [optional]
 * @return string
 */
function UNCPath($filepath, $utf8_decode=true)
{
	GLOBAL $netDrive;
	$drv = substr($filepath, 0, 2);
	if (isset($netDrive[$drv])) $filepath = sprintf("%s%s", $netDrive[$drv], substr($filepath, 2));
    if ($utf8_decode) return mb_convert_encoding($filepath, "CP1252", "UTF-8");
	return $filepath;
}

function mapToUtf8($str)
{
    return mb_convert_encoding($str, "UTF-8", "CP1252");
}



/**
 * (PHP 4, PHP 5)<br/>
 * Open directory handle
 * @link http://php.net/manual/en/function.opendir.php
 * @param string $path <p>
 * The directory path that is to be opened
 * </p>
 * @param resource $context [optional] <p>
 * For a description of the context parameter,
 * refer to the streams section of
 * the manual.
 * </p>
 * @return resource a directory handle resource on success, or
 * false on failure.
 * </p>
 * <p>
 * If path is not a valid directory or the
 * directory can not be opened due to permission restrictions or
 * filesystem errors, opendir returns false and
 * generates a PHP error of level
 * E_WARNING. You can suppress the error output of
 * opendir by prepending
 * '@' to the
 * front of the function name.
 */
function nopendir ($path, $context = null)
{
    if (is_null($context))
        return @opendir(UNCPath($path));
    else
        return @opendir(UNCPath($path), $context);
}


/**
 * (PHP 4, PHP 5)<br/>
 * Change directory
 * @link http://php.net/manual/en/function.chdir.php
 * @param string $directory <p>
 * The new current directory
 * </p>
 * @return bool true on success or false on failure.
 */
function nchdir ($directory)
{
    return chdir(UNCPath($directory));
}

/**
 * (PHP 4, PHP 5)<br/>
 * Return an instance of the Directory class
 * @link http://php.net/manual/en/class.dir.php
 * @param $directory
 * @param $context [optional]
 * @return Directory
 */
function ndir ($directory, $context=null)
{
    if (is_null($context))
        return @dir(UNCPath($directory));
    else
        return @dir(UNCPath($directory), $context);
}

/**
 * (PHP 5)<br/>
 * List files and directories inside the specified path
 * @link http://php.net/manual/en/function.scandir.php
 * @param string $directory <p>
 * The directory that will be scanned.
 * </p>
 * @param int $sorting_order [optional] <p>
 * By default, the sorted order is alphabetical in ascending order. If
 * the optional sorting_order is set to non-zero,
 * then the sort order is alphabetical in descending order.
 * </p>
 * @param resource $context [optional] <p>
 * For a description of the context parameter,
 * refer to the streams section of
 * the manual.
 * </p>
 * @return array an array of filenames on success, or false on
 * failure. If directory is not a directory, then
 * boolean false is returned, and an error of level
 * E_WARNING is generated.
 */
function nscandir($directory, $sorting_order = null, $context = null)
{
    if (is_null($sorting_order) && is_null($context))
        return scandir(UNCPath($directory));
    else if (is_null($context))
        return scandir(UNCPath($directory), $sorting_order);
    else
        return scandir(UNCPath($directory), $sorting_order, $context);
}

/**
 * (PHP 4 &gt;= 4.3.0, PHP 5)<br/>
 * Find pathnames matching a pattern
 * @link http://php.net/manual/en/function.glob.php
 * @param string $pattern <p>
 * The pattern. No tilde expansion or parameter substitution is done.
 * </p>
 * @param int $flags [optional] <p>
 * Valid flags:
 * GLOB_MARK - Adds a slash to each directory returned
 * @return array an array containing the matched files/directories, an empty array
 * if no file matched or false on error.
 * </p>
 * <p>
 * On some systems it is impossible to distinguish between empty match and an
 * error.
 */
function nglob ($pattern, $flags = null)
{
    if (is_null($flags))
        return array_map("mapToUtf8", @glob(UNCPath($pattern)));
    else
        return  array_map("mapToUtf8", @glob(UNCPath($pattern), $flags));
}

/**
 * (PHP 4, PHP 5)<br/>
 * Gets last access time of file
 * @link http://php.net/manual/en/function.fileatime.php
 * @param string $filename <p>
 * Path to the file.
 * </p>
 * @return int the time the file was last accessed, or false on failure.
 * The time is returned as a Unix timestamp.
 */
function nfileatime ($filename)
{
    return fileatime (UNCPath($filename));
}

/**
 * (PHP 4, PHP 5)<br/>
 * Gets inode change time of file
 * @link http://php.net/manual/en/function.filectime.php
 * @param string $filename <p>
 * Path to the file.
 * </p>
 * @return int the time the file was last changed, or false on failure.
 * The time is returned as a Unix timestamp.
 */
function nfilectime ($filename)
{
    return filectime (UNCPath($filename));
}

/**
 * (PHP 4, PHP 5)<br/>
 * Gets file group
 * @link http://php.net/manual/en/function.filegroup.php
 * @param string $filename <p>
 * Path to the file.
 * </p>
 * @return int the group ID of the file, or false in case
 * of an error. The group ID is returned in numerical format, use
 * posix_getgrgid to resolve it to a group name.
 * Upon failure, false is returned.
 */
function nfilegroup ($filename)
{
    return nfilegroup (UNCPath($filename));
}

/**
 * (PHP 4, PHP 5)<br/>
 * Gets file inode
 * @link http://php.net/manual/en/function.fileinode.php
 * @param string $filename <p>
 * Path to the file.
 * </p>
 * @return int the inode number of the file, or false on failure.
 */
function nfileinode ($filename)
{
    return fileinode (UNCPath($filename));
}

/**
 * (PHP 4, PHP 5)<br/>
 * Gets file modification time
 * @link http://php.net/manual/en/function.filemtime.php
 * @param string $filename <p>
 * Path to the file.
 * </p>
 * @return int the time the file was last modified, or false on failure.
 * The time is returned as a Unix timestamp, which is
 * suitable for the date function.
 */
function nfilemtime ($filename)
{
    return filemtime (UNCPath($filename));
}

/**
 * (PHP 4, PHP 5)<br/>
 * Gets file owner
 * @link http://php.net/manual/en/function.fileowner.php
 * @param string $filename <p>
 * Path to the file.
 * </p>
 * @return int the user ID of the owner of the file, or false on failure.
 * The user ID is returned in numerical format, use
 * posix_getpwuid to resolve it to a username.
 */
function nfileowner ($filename)
{
    return  fileowner (UNCPath($filename));
}

/**
 * (PHP 4, PHP 5)<br/>
 * Gets file permissions
 * @link http://php.net/manual/en/function.fileperms.php
 * @param string $filename <p>
 * Path to the file.
 * </p>
 * @return int the permissions on the file, or false on failure.
 */
function nfileperms ($filename)
{
    return fileperms (UNCPath($filename));
}

/**
 * (PHP 4, PHP 5)<br/>
 * Gets file size
 * @link http://php.net/manual/en/function.filesize.php
 * @param string $filename <p>
 * Path to the file.
 * </p>
 * @return int the size of the file in bytes, or false (and generates an error
 * of level E_WARNING) in case of an error.
 */
function nfilesize ($filename)
{
    return filesize (UNCPath($filename));
}

/**
 * (PHP 4, PHP 5)<br/>
 * Gets file type
 * @link http://php.net/manual/en/function.filetype.php
 * @param string $filename <p>
 * Path to the file.
 * </p>
 * @return string the type of the file. Possible values are fifo, char,
 * dir, block, link, file, socket and unknown.
 * </p>
 * <p>
 * Returns false if an error occurs. filetype will also
 * produce an E_NOTICE message if the stat call fails
 * or if the file type is unknown.
 */
function nfiletype ($filename)
{
    return filetype (UNCPath($filename));
}

/**
 * (PHP 4, PHP 5)<br/>
 * Checks whether a file or directory exists
 * @link http://php.net/manual/en/function.file-exists.php
 * @param string $filename <p>
 * Path to the file or directory.
 * </p>
 * <p>
 * On windows, use //computername/share/filename or
 * \\computername\share\filename to check files on
 * network shares.
 * </p>
 * @return bool true if the file or directory specified by
 * filename exists; false otherwise.
 * </p>
 * <p>
 * This function will return false for symlinks pointing to non-existing
 * files.
 * </p>
 * <p>
 * This function returns false for files inaccessible due to safe mode restrictions. However these
 * files still can be included if
 * they are located in safe_mode_include_dir.
 * </p>
 * <p>
 * The check is done using the real UID/GID instead of the effective one.
 */
function nfile_exists ($filename)
{
    return @file_exists (UNCPath($filename));
}

/**
 * (PHP 4, PHP 5)<br/>
 * Tells whether the filename is writable
 * @link http://php.net/manual/en/function.is-writable.php
 * @param string $filename <p>
 * The filename being checked.
 * </p>
 * @return bool true if the filename exists and is
 * writable.
 */
function nis_writable ($filename)
{
    return is_writable (UNCPath($filename));
}

/**
 * (PHP 4, PHP 5)<br/>
 * &Alias; <function>is_writable</function>
 * @link http://php.net/manual/en/function.is-writeable.php
 * @param string $filename <p>
 * The filename being checked.
 * </p>
 * @return bool true if the filename exists and is
 * writable.
 */
function nis_writeable ($filename)
{
    return is_writeable (UNCPath($filename));
}

/**
 * (PHP 4, PHP 5)<br/>
 * Tells whether a file exists and is readable
 * @link http://php.net/manual/en/function.is-readable.php
 * @param string $filename <p>
 * Path to the file.
 * </p>
 * @return bool true if the file or directory specified by
 * filename exists and is readable, false otherwise.
 */
function nis_readable ($filename)
{
    return @is_readable (UNCPath($filename));
}

/**
 * (PHP 4, PHP 5)<br/>
 * Tells whether the filename is executable
 * @link http://php.net/manual/en/function.is-executable.php
 * @param string $filename <p>
 * Path to the file.
 * </p>
 * @return bool true if the filename exists and is executable, or false on
 * error.
 */
function nis_executable ($filename)
{
    return is_executable (UNCPath($filename));
}

/**
 * (PHP 4, PHP 5)<br/>
 * Tells whether the filename is a regular file
 * @link http://php.net/manual/en/function.is-file.php
 * @param string $filename <p>
 * Path to the file.
 * </p>
 * @return bool true if the filename exists and is a regular file, false
 * otherwise.
 */
function nis_file ($filename)
{
    return @is_file (UNCPath($filename));
}

/**
 * (PHP 4, PHP 5)<br/>
 * Tells whether the filename is a directory
 * @link http://php.net/manual/en/function.is-dir.php
 * @param string $filename <p>
 * Path to the file. If filename is a relative
 * filename, it will be checked relative to the current working
 * directory. If filename is a symbolic or hard link
 * then the link will be resolved and checked.
 * </p>
 * @return bool true if the filename exists and is a directory, false
 * otherwise.
 */
function nis_dir ($filename)
{
    return @is_dir (UNCPath($filename));
}

/**
 * (PHP 4, PHP 5)<br/>
 * Tells whether the filename is a symbolic link
 * @link http://php.net/manual/en/function.is-link.php
 * @param string $filename <p>
 * Path to the file.
 * </p>
 * @return bool true if the filename exists and is a symbolic link, false
 * otherwise.
 */
function nis_link ($filename)
{
    return is_link (UNCPath($filename));
}

/**
 * (PHP 4, PHP 5)<br/>
 * Gives information about a file
 * @link http://php.net/manual/en/function.stat.php
 * @param string $filename <p>
 * Path to the file.
 * </p>
 * @return array <table>
 * stat and fstat result
 * format
 * <tr valign="top">
 * <td>Numeric</td>
 * <td>Associative (since PHP 4.0.6)</td>
 * <td>Description</td>
 * </tr>
 * <tr valign="top">
 * <td>0</td>
 * <td>dev</td>
 * <td>device number</td>
 * </tr>
 * <tr valign="top">
 * <td>1</td>
 * <td>ino</td>
 * <td>inode number *</td>
 * </tr>
 * <tr valign="top">
 * <td>2</td>
 * <td>mode</td>
 * <td>inode protection mode</td>
 * </tr>
 * <tr valign="top">
 * <td>3</td>
 * <td>nlink</td>
 * <td>number of links</td>
 * </tr>
 * <tr valign="top">
 * <td>4</td>
 * <td>uid</td>
 * <td>userid of owner *</td>
 * </tr>
 * <tr valign="top">
 * <td>5</td>
 * <td>gid</td>
 * <td>groupid of owner *</td>
 * </tr>
 * <tr valign="top">
 * <td>6</td>
 * <td>rdev</td>
 * <td>device type, if inode device</td>
 * </tr>
 * <tr valign="top">
 * <td>7</td>
 * <td>size</td>
 * <td>size in bytes</td>
 * </tr>
 * <tr valign="top">
 * <td>8</td>
 * <td>atime</td>
 * <td>time of last access (Unix timestamp)</td>
 * </tr>
 * <tr valign="top">
 * <td>9</td>
 * <td>mtime</td>
 * <td>time of last modification (Unix timestamp)</td>
 * </tr>
 * <tr valign="top">
 * <td>10</td>
 * <td>ctime</td>
 * <td>time of last inode change (Unix timestamp)</td>
 * </tr>
 * <tr valign="top">
 * <td>11</td>
 * <td>blksize</td>
 * <td>blocksize of filesystem IO **</td>
 * </tr>
 * <tr valign="top">
 * <td>12</td>
 * <td>blocks</td>
 * <td>number of 512-byte blocks allocated **</td>
 * </tr>
 * </table>
 * * On Windows this will always be 0.
 * </p>
 * <p>
 * ** Only valid on systems supporting the st_blksize type - other
 * systems (e.g. Windows) return -1.
 * </p>
 * <p>
 * In case of error, stat returns false.
 */
function nstat ($filename)
{
    return @stat (UNCPath($filename));
}

/**
 * (PHP 4, PHP 5)<br/>
 * Gives information about a file or symbolic link
 * @link http://php.net/manual/en/function.lstat.php
 * @param string $filename <p>
 * Path to a file or a symbolic link.
 * </p>
 * @return array See the manual page for stat for information on
 * the structure of the array that lstat returns.
 * This function is identical to the stat function
 * except that if the filename parameter is a symbolic
 * link, the status of the symbolic link is returned, not the status of the
 * file pointed to by the symbolic link.
 */
function nlstat ($filename)
{
    return lstat (UNCPath($filename));
}

/**
 * (PHP 4, PHP 5)<br/>
 * Outputs a file
 * @link http://php.net/manual/en/function.readfile.php
 * @param string $filename <p>
 * The filename being read.
 * </p>
 * @param bool $use_include_path [optional] <p>
 * You can use the optional second parameter and set it to true, if
 * you want to search for the file in the include_path, too.
 * </p>
 * @param resource $context [optional] <p>
 * A context stream resource.
 * </p>
 * @return int the number of bytes read from the file. If an error
 * occurs, false is returned and unless the function was called as
 * @readfile, an error message is printed.
 */
function nreadfile ($filename, $use_include_path = null, $context = null)
{
    if (is_null($context) && is_null($use_include_path))
        return readfile (UNCPath($filename));
    else if (is_null($context))
        return readfile (UNCPath($filename), $use_include_path);
    else
        return readfile (UNCPath($filename), $use_include_path, $context);
}

/**
 * (PHP 4, PHP 5)<br/>
 * Removes directory
 * @link http://php.net/manual/en/function.rmdir.php
 * @param string $dirname <p>
 * Path to the directory.
 * </p>
 * @param resource $context [optional] &note.context-support;
 * @return bool true on success or false on failure.
 */
function nrmdir ($dirname, $context = null)
{
    if (is_null($context))
        return @rmdir (UNCPath($dirname));
    else
        return @rmdir (UNCPath($dirname), $context);
}


/**
 * (PHP 4, PHP 5)<br/>
 * Gets line from file pointer
 * @link http://php.net/manual/en/function.fgets.php
 * @param resource $handle &fs.validfp.all;
 * @param int $length [optional] <p>
 * Reading ends when length - 1 bytes have been
 * read, on a newline (which is included in the return value), or on EOF
 * (whichever comes first). If no length is specified, it will keep
 * reading from the stream until it reaches the end of the line.
 * </p>
 * <p>
 * Until PHP 4.3.0, omitting it would assume 1024 as the line length.
 * If the majority of the lines in the file are all larger than 8KB,
 * it is more resource efficient for your script to specify the maximum
 * line length.
 * </p>
 * @return string a string of up to length - 1 bytes read from
 * the file pointed to by handle.
 * </p>
 * <p>
 * If an error occurs, returns false.
 */
function fgets_utf8 ($handle, $length = null)
{
    if (is_null($length))
        $res = fgets($handle);
    else
        $res = fgets($handle, $length);
    if ($res === false) return $res;
    return utf8_encode($res);
}



/**
 * (PHP 4, PHP 5)<br/>
 * Opens file or URL
 * @link http://php.net/manual/en/function.fopen.php
 * @param string $filename <p>
 * If filename is of the form "scheme://...", it
 * is assumed to be a URL and PHP will search for a protocol handler
 * (also known as a wrapper) for that scheme. If no wrappers for that
 * protocol are registered, PHP will emit a notice to help you track
 * potential problems in your script and then continue as though
 * filename specifies a regular file.
 * </p>
 * <p>
 * If PHP has decided that filename specifies
 * a local file, then it will try to open a stream on that file.
 * The file must be accessible to PHP, so you need to ensure that
 * the file access permissions allow this access.
 * If you have enabled &safemode;,
 * or open_basedir further
 * restrictions may apply.
 * </p>
 * <p>
 * If PHP has decided that filename specifies
 * a registered protocol, and that protocol is registered as a
 * network URL, PHP will check to make sure that
 * allow_url_fopen is
 * enabled. If it is switched off, PHP will emit a warning and
 * the fopen call will fail.
 * </p>
 * <p>
 * The list of supported protocols can be found in . Some protocols (also referred to as
 * wrappers) support context
 * and/or &php.ini; options. Refer to the specific page for the
 * protocol in use for a list of options which can be set. (e.g.
 * &php.ini; value user_agent used by the
 * http wrapper).
 * </p>
 * <p>
 * On the Windows platform, be careful to escape any backslashes
 * used in the path to the file, or use forward slashes.
 * ]]>
 * </p>
 * @param string $mode <p>
 * The mode parameter specifies the type of access
 * you require to the stream. It may be any of the following:
 * <table>
 * A list of possible modes for fopen
 * using mode
 * <tr valign="top">
 * <td>mode</td>
 * <td>Description</td>
 * </tr>
 * <tr valign="top">
 * <td>'r'</td>
 * <td>
 * Open for reading only; place the file pointer at the
 * beginning of the file.
 * </td>
 * </tr>
 * <tr valign="top">
 * <td>'r+'</td>
 * <td>
 * Open for reading and writing; place the file pointer at
 * the beginning of the file.
 * </td>
 * </tr>
 * <tr valign="top">
 * <td>'w'</td>
 * <td>
 * Open for writing only; place the file pointer at the
 * beginning of the file and truncate the file to zero length.
 * If the file does not exist, attempt to create it.
 * </td>
 * </tr>
 * <tr valign="top">
 * <td>'w+'</td>
 * <td>
 * Open for reading and writing; place the file pointer at
 * the beginning of the file and truncate the file to zero
 * length. If the file does not exist, attempt to create it.
 * </td>
 * </tr>
 * <tr valign="top">
 * <td>'a'</td>
 * <td>
 * Open for writing only; place the file pointer at the end of
 * the file. If the file does not exist, attempt to create it.
 * </td>
 * </tr>
 * <tr valign="top">
 * <td>'a+'</td>
 * <td>
 * Open for reading and writing; place the file pointer at
 * the end of the file. If the file does not exist, attempt to
 * create it.
 * </td>
 * </tr>
 * <tr valign="top">
 * <td>'x'</td>
 * <td>
 * Create and open for writing only; place the file pointer at the
 * beginning of the file. If the file already exists, the
 * fopen call will fail by returning false and
 * generating an error of level E_WARNING. If
 * the file does not exist, attempt to create it. This is equivalent
 * to specifying O_EXCL|O_CREAT flags for the
 * underlying open(2) system call.
 * </td>
 * </tr>
 * <tr valign="top">
 * <td>'x+'</td>
 * <td>
 * Create and open for reading and writing; place the file pointer at
 * the beginning of the file. If the file already exists, the
 * fopen call will fail by returning false and
 * generating an error of level E_WARNING. If
 * the file does not exist, attempt to create it. This is equivalent
 * to specifying O_EXCL|O_CREAT flags for the
 * underlying open(2) system call.
 * </td>
 * </tr>
 * </table>
 * </p>
 * <p>
 * Different operating system families have different line-ending
 * conventions. When you write a text file and want to insert a line
 * break, you need to use the correct line-ending character(s) for your
 * operating system. Unix based systems use \n as the
 * line ending character, Windows based systems use \r\n
 * as the line ending characters and Macintosh based systems use
 * \r as the line ending character.
 * </p>
 * <p>
 * If you use the wrong line ending characters when writing your files, you
 * might find that other applications that open those files will "look
 * funny".
 * </p>
 * <p>
 * Windows offers a text-mode translation flag ('t')
 * which will transparently translate \n to
 * \r\n when working with the file. In contrast, you
 * can also use 'b' to force binary mode, which will not
 * translate your data. To use these flags, specify either
 * 'b' or 't' as the last character
 * of the mode parameter.
 * </p>
 * <p>
 * The default translation mode depends on the SAPI and version of PHP that
 * you are using, so you are encouraged to always specify the appropriate
 * flag for portability reasons. You should use the 't'
 * mode if you are working with plain-text files and you use
 * \n to delimit your line endings in your script, but
 * expect your files to be readable with applications such as notepad. You
 * should use the 'b' in all other cases.
 * </p>
 * <p>
 * If you do not specify the 'b' flag when working with binary files, you
 * may experience strange problems with your data, including broken image
 * files and strange problems with \r\n characters.
 * </p>
 * <p>
 * For portability, it is strongly recommended that you always
 * use the 'b' flag when opening files with fopen.
 * </p>
 * <p>
 * Again, for portability, it is also strongly recommended that
 * you re-write code that uses or relies upon the 't'
 * mode so that it uses the correct line endings and
 * 'b' mode instead.
 * </p>
 * @param bool $use_include_path [optional] <p>
 * The optional third use_include_path parameter
 * can be set to '1' or true if you want to search for the file in the
 * include_path, too.
 * </p>
 * @param resource $context [optional] &note.context-support;
 * @return resource a file pointer resource on success, or false on error.
 */
function nfopen ($filename, $mode)
{
    return @fopen (UNCPath($filename), $mode);
}

/**
 * (PHP 4, PHP 5)<br/>
 * Attempts to create the directory specified by pathname.
 * @link http://php.net/manual/en/function.mkdir.php
 * @param string $pathname <p>
 * The directory path.
 * </p>
 * @param int $mode [optional] <p>
 * The mode is 0777 by default, which means the widest possible
 * access. For more information on modes, read the details
 * on the chmod page.
 * </p>
 * <p>
 * mode is ignored on Windows.
 * </p>
 * <p>
 * Note that you probably want to specify the mode as an octal number,
 * which means it should have a leading zero. The mode is also modified
 * by the current umask, which you can change using
 * umask().
 * </p>
 * @param bool $recursive [optional] <p>
 * Allows the creation of nested directories specified in the pathname. Default to false.
 * </p>
 * @param resource $context [optional] &note.context-support;
 * @return bool true on success or false on failure.
 */
function nmkdir ($pathname, $mode = null)
{
    if (is_null($mode))
        return @mkdir (UNCPath($pathname));
    else
        return @mkdir (UNCPath($pathname), $mode);
}

/**
 * (PHP 4, PHP 5)<br/>
 * Renames a file or directory
 * @link http://php.net/manual/en/function.rename.php
 * @param string $oldname <p>
 * </p>
 * <p>
 * The old name. The wrapper used in oldname
 * must match the wrapper used in
 * newname.
 * </p>
 * @param string $newname <p>
 * The new name.
 * </p>
 * @param resource $context [optional] &note.context-support;
 * @return bool true on success or false on failure.
 */
function nrename ($oldname, $newname, $context = null)
{
    if (is_null($context))
        return @rename (UNCPath($oldname), UNCPath($newname));
    else
        return @rename (UNCPath($oldname), UNCPath($newname), $context);
}

/**
 * (PHP 4, PHP 5)<br/>
 * Copies file
 * @link http://php.net/manual/en/function.copy.php
 * @param string $source <p>
 * Path to the source file.
 * </p>
 * @param string $dest <p>
 * The destination path. If dest is a URL, the
 * copy operation may fail if the wrapper does not support overwriting of
 * existing files.
 * </p>
 * <p>
 * If the destination file already exists, it will be overwritten.
 * </p>
 * @param resource $context [optional] <p>
 * A valid context resource created with
 * stream_context_create.
 * </p>
 * @return bool true on success or false on failure.
 */
function ncopy ($source, $dest, $context = null)
{
    if (is_null($context))
        return @copy (UNCPath($source), UNCPath($dest));
    else
        return @copy (UNCPath($source), UNCPath($dest), $context);
}

/**
 * (PHP 4, PHP 5)<br/>
 * Create file with unique file name
 * @link http://php.net/manual/en/function.tempnam.php
 * @param string $dir <p>
 * The directory where the temporary filename will be created.
 * </p>
 * @param string $prefix <p>
 * The prefix of the generated temporary filename.
 * </p>
 * Windows use only the first three characters of prefix.
 * @return string the new temporary filename, or false on
 * failure.
 */
function ntempnam ($dir, $prefix)
{
    return tempnam (UNCPath($dir), $prefix);
}


/**
 * (PHP 4, PHP 5)<br/>
 * Reads entire file into an array
 * @link http://php.net/manual/en/function.file.php
 * @param string $filename <p>
 * Path to the file.
 * </p>
 * &tip.fopen-wrapper;
 * @param int $flags [optional] <p>
 * The optional parameter flags can be one, or
 * more, of the following constants:
 * FILE_USE_INCLUDE_PATH
 * Search for the file in the include_path.
 * @param resource $context [optional] <p>
 * A context resource created with the
 * stream_context_create function.
 * </p>
 * <p>
 * &note.context-support;
 * </p>
 * @return array the file in an array. Each element of the array corresponds to a
 * line in the file, with the newline still attached. Upon failure,
 * file returns false.
 * </p>
 * <p>
 * Each line in the resulting array will include the line ending, unless
 * FILE_IGNORE_NEW_LINES is used, so you still need to
 * use rtrim if you do not want the line ending
 * present.
 */
function nfile ($filename, $flags = null, $context = null)
{
    if (is_null($flags) && is_null($context))
        return @file(UNCPath($filename));
    else if (is_null($context))
        return @file(UNCPath($filename), $flags);
    else
        return @file(UNCPath($filename), $flags, $context);
}

/**
 * (PHP 4 &gt;= 4.3.0, PHP 5)<br/>
 * Reads entire file into a string
 * @link http://php.net/manual/en/function.file-get-contents.php
 * @param string $filename <p>
 * Name of the file to read.
 * </p>
 * @param int $flags [optional] <p>
 * Prior to PHP 6, this parameter is called
 * use_include_path and is a bool.
 * As of PHP 5 the FILE_USE_INCLUDE_PATH can be used
 * to trigger include path
 * search.
 * </p>
 * <p>
 * The value of flags can be any combination of
 * the following flags (with some restrictions), joined with the
 * binary OR (|)
 * operator.
 * </p>
 * <p>
 * <table>
 * Available flags
 * <tr valign="top">
 * <td>Flag</td>
 * <td>Description</td>
 * </tr>
 * <tr valign="top">
 * <td>
 * FILE_USE_INCLUDE_PATH
 * </td>
 * <td>
 * Search for filename in the include directory.
 * See include_path for more
 * information.
 * </td>
 * </tr>
 * <tr valign="top">
 * <td>
 * FILE_TEXT
 * </td>
 * <td>
 * As of PHP 6, the default encoding of the read
 * data is UTF-8. You can specify a different encoding by creating a
 * custom context or by changing the default using
 * stream_default_encoding. This flag cannot be
 * used with FILE_BINARY.
 * </td>
 * </tr>
 * <tr valign="top">
 * <td>
 * FILE_BINARY
 * </td>
 * <td>
 * With this flag, the file is read in binary mode. This is the default
 * setting and cannot be used with FILE_TEXT.
 * </td>
 * </tr>
 * </table>
 * </p>
 * @param resource $context [optional] <p>
 * A valid context resource created with
 * stream_context_create. If you don't need to use a
 * custom context, you can skip this parameter by &null;.
 * </p>
 * @param int $offset [optional] <p>
 * The offset where the reading starts.
 * </p>
 * @param int $maxlen [optional] <p>
 * Maximum length of data read. The default is to read until end
 * of file is reached.
 * </p>
 * @return string The function returns the read data or false on failure.
 */
function nfile_get_contents ($filename, $flags = null, $context = null, $offset = null, $maxlen = null)
{
    if (is_null($flags) && is_null($context) && is_null($offset) && is_null($maxlen))
        return @file_get_contents (UNCPath($filename));
    else if (is_null($context) && is_null($offset) && is_null($maxlen))
        return @file_get_contents (UNCPath($filename), $flags);
    else if (is_null($offset) && is_null($maxlen))
        return @file_get_contents (UNCPath($filename), $flags, $context);
    else if (is_null($maxlen))
        return @file_get_contents (UNCPath($filename), $flags, $context, $offset);
    else
        return @file_get_contents (UNCPath($filename), $flags, $context, $offset, $maxlen);
}

/**
 * (PHP 5)<br/>
 * Write a string to a file
 * @link http://php.net/manual/en/function.file-put-contents.php
 * @param string $filename <p>
 * Path to the file where to write the data.
 * </p>
 * @param mixed $data <p>
 * The data to write. Can be either a string, an
 * array or a stream resource.
 * </p>
 * <p>
 * If data is a stream resource, the
 * remaining buffer of that stream will be copied to the specified file.
 * This is similar with using stream_copy_to_stream.
 * </p>
 * <p>
 * You can also specify the data parameter as a single
 * dimension array. This is equivalent to
 * file_put_contents($filename, implode('', $array)).
 * </p>
 * @param int $flags [optional] <p>
 * The value of flags can be any combination of
 * the following flags (with some restrictions), joined with the binary OR
 * (|) operator.
 * </p>
 * <p>
 * <table>
 * Available flags
 * <tr valign="top">
 * <td>Flag</td>
 * <td>Description</td>
 * </tr>
 * <tr valign="top">
 * <td>
 * FILE_USE_INCLUDE_PATH
 * </td>
 * <td>
 * Search for filename in the include directory.
 * See include_path for more
 * information.
 * </td>
 * </tr>
 * <tr valign="top">
 * <td>
 * FILE_APPEND
 * </td>
 * <td>
 * If file filename already exists, append
 * the data to the file instead of overwriting it. Mutually
 * exclusive with LOCK_EX since appends are atomic and thus there
 * is no reason to lock.
 * </td>
 * </tr>
 * <tr valign="top">
 * <td>
 * LOCK_EX
 * </td>
 * <td>
 * Acquire an exclusive lock on the file while proceeding to the
 * writing. Mutually exclusive with FILE_APPEND.
 * </td>
 * </tr>
 * <tr valign="top">
 * <td>
 * FILE_TEXT
 * </td>
 * <td>
 * data is written in text mode. If unicode
 * semantics are enabled, the default encoding is UTF-8.
 * You can specify a different encoding by creating a custom context
 * or by using the stream_default_encoding to
 * change the default. This flag cannot be used with
 * FILE_BINARY. This flag is only available since
 * PHP 6.
 * </td>
 * </tr>
 * <tr valign="top">
 * <td>
 * FILE_BINARY
 * </td>
 * <td>
 * data will be written in binary mode. This
 * is the default setting and cannot be used with
 * FILE_TEXT. This flag is only available since
 * PHP 6.
 * </td>
 * </tr>
 * </table>
 * </p>
 * @param resource $context [optional] <p>
 * A valid context resource created with
 * stream_context_create.
 * </p>
 * @return int The function returns the number of bytes that were written to the file, or
 * false on failure.
 */
function nfile_put_contents ($filename, $data, $flags = null, $context = null)
{
    if (is_null($flags) && is_null($context))
        return @file_put_contents (UNCPath($filename), $data);
    else if (is_null($context))
        return @file_put_contents (UNCPath($filename), $data, $flags);
    else
        return @file_put_contents (UNCPath($filename), $data, $flags, $context);
}

/**
 * (PHP 4, PHP 5)<br/>
 * Deletes a file
 * @link http://php.net/manual/en/function.unlink.php
 * @param string $filename <p>
 * Path to the file.
 * </p>
 * @param resource $context [optional] &note.context-support;
 * @return bool true on success or false on failure.
 */
function nunlink ($filename, $context = null)
{
    if (is_null($context)) return @unlink(UNCPath($filename));
    return @unlink(UNCPath($filename), $context);
}
/**
 * (PHP 4, PHP 5)<br/>
 * Gets line from file pointer and parse for CSV fields
 * @link http://php.net/manual/en/function.fgetcsv.php
 * @param resource $handle <p>
 * A valid file pointer to a file successfully opened by
 * fopen, popen, or
 * fsockopen.
 * </p>
 * @param int $length [optional] <p>
 * Must be greater than the longest line (in characters) to be found in
 * the CSV file (allowing for trailing line-end characters). It became
 * optional in PHP 5. Omitting this parameter (or setting it to 0 in PHP
 * 5.0.4 and later) the maximum line length is not limited, which is
 * slightly slower.
 * </p>
 * @param string $delimiter [optional] <p>
 * Set the field delimiter (one character only).
 * </p>
 * @param string $enclosure [optional] <p>
 * Set the field enclosure character (one character only).
 * </p>
 * @param string $escape [optional] <p>
 * Set the escape character (one character only). Defaults as a backslash.
 * </p>
 * @return array an indexed array containing the fields read.
 * </p>
 * <p>
 * A blank line in a CSV file will be returned as an array
 * comprising a single null field, and will not be treated
 * as an error.
 * </p>
 * &note.line-endings;
 * <p>
 * fgetcsv returns &null; if an invalid
 * handle is supplied or false on other errors,
 * including end of file.
 */
function fgetcsv_utf8 ($handle, $length = null, $delimiter = null, $enclosure = null, $escape = null)
{
    if (is_null($length) && is_null($delimiter) && is_null($enclosure) && is_null($escape))
        $ar = fgetcsv ($handle);
    else if (is_null($delimiter) && is_null($enclosure) && is_null($escape))
        $ar = fgetcsv ($handle, $length);
    else if (is_null($enclosure) && is_null($escape))
        $ar = fgetcsv ($handle, $length, $delimiter);
    else if (is_null($escape))
        $ar = fgetcsv ($handle, $length, $delimiter, $enclosure);
    else
        $ar = fgetcsv ($handle, $length, $delimiter, $enclosure, $escape);
    if ($ar === false) return $ar;
    return array_map('utf8_encode_csv', $ar);
}

/**
 * Encode CSV Daten nach UTF8
 *
 * @param string    $str
 * @return string
 */
function utf8_encode_csv($str)
{
    return mb_convert_encoding( rtrim(str_replace(chr(31), "", $str), " "), 'UTF-8', 'CP1252');
}
