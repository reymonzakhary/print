<?php

namespace App\Enums;

use EmreYarligan\EnumConcern\EnumConcern;

enum FileExtensions: string
{
    use EnumConcern;

    // Documents
    case TXT = 'txt';
    case PDF = 'pdf';
    case DOC = 'doc';
    case DOCX = 'docx';
    case XLS = 'xls';
    case XLSX = 'xlsx';
    case PPT = 'ppt';
    case PPTX = 'pptx';
    case ODT = 'odt';
    case ODS = 'ods';
    case ODP = 'odp';
    case RTF = 'rtf';
    case TEX = 'tex';

    // Images
    case JPG = 'jpg';
    case JPEG = 'jpeg';
    case PNG = 'png';
    case GIF = 'gif';
    case BMP = 'bmp';
    case SVG = 'svg';
    case WEBP = 'webp';
    case ICO = 'ico';
    case TIFF = 'tiff';
    case TIF = 'tif';
    case PSD = 'psd';
    case AI = 'ai';
    case EPS = 'eps';
    case RAW = 'raw';
    case HEIC = 'heic';
    case HEIF = 'heif';

    // Videos
    case MP4 = 'mp4';
    case AVI = 'avi';
    case MKV = 'mkv';
    case MOV = 'mov';
    case WMV = 'wmv';
    case FLV = 'flv';
    case WEBM = 'webm';
    case MPG = 'mpg';
    case MPEG = 'mpeg';
    case M4V = 'm4v';
    case THREE_GP = '3gp';

    // Audio
    case MP3 = 'mp3';
    case WAV = 'wav';
    case FLAC = 'flac';
    case AAC = 'aac';
    case OGG = 'ogg';
    case WMA = 'wma';
    case M4A = 'm4a';
    case OPUS = 'opus';
    case AIFF = 'aiff';
    case APE = 'ape';

    // Archives
    case ZIP = 'zip';
    case RAR = 'rar';
    case SEVEN_Z = '7z';
    case TAR = 'tar';
    case GZ = 'gz';
//    case BZ2 = 'bz2';
//    case XZ = 'xz';
//    case ISO = 'iso';
//    case DMG = 'dmg';
//    case CAB = 'cab';

    // Code & Development
    case HTML = 'html';
    case HTM = 'htm';
    case CSS = 'css';
    case JS = 'js';
    case TS = 'ts';
    case JSX = 'jsx';
    case TSX = 'tsx';
    case PHP = 'php';
//    case PY = 'py';
//    case JAVA = 'java';
//    case C = 'c';
//    case CPP = 'cpp';
//    case CS = 'cs';
//    case H = 'h';
//    case HPP = 'hpp';
//    case SWIFT = 'swift';
//    case KT = 'kt';
//    case GO = 'go';
//    case RS = 'rs';
//    case RB = 'rb';
//    case PL = 'pl';
//    case SCALA = 'scala';
//    case R = 'r';
//    case DART = 'dart';
//    case LUA = 'lua';
    case VUE = 'vue';
    case SCSS = 'scss';
    case SASS = 'sass';
    case LESS = 'less';

    // Data & Config
    case JSON = 'json';
    case XML = 'xml';
    case YAML = 'yaml';
    case YML = 'yml';
    case CSV = 'csv';
    case TSV = 'tsv';
    case SQL = 'sql';
    case DB = 'db';
    case SQLITE = 'sqlite';
    case TOML = 'toml';
//    case INI = 'ini';
//    case CFG = 'cfg';
    case CONF = 'conf';
    case ENV = 'env';
//    case PROPERTIES = 'properties';

    // Markup & Documentation
    case MD = 'md';
    case MARKDOWN = 'markdown';
    case RST = 'rst';
    case ADOC = 'adoc';
    case TEXTILE = 'textile';

    // Shell & Scripts
//    case SH = 'sh';
//    case BASH = 'bash';
//    case ZSH = 'zsh';
//    case FISH = 'fish';
//    case PS1 = 'ps1';
//    case BAT = 'bat';
//    case CMD = 'cmd';

    // Binary & Executable
//    case EXE = 'exe';
//    case DLL = 'dll';
//    case SO = 'so';
//    case DYLIB = 'dylib';
//    case APP = 'app';
//    case DEB = 'deb';
//    case RPM = 'rpm';
    case APK = 'apk';
    case IPA = 'ipa';
//    case MSI = 'msi';
//    case JAR = 'jar';
//    case WAR = 'war';

    // Fonts
    case TTF = 'ttf';
    case OTF = 'otf';
    case WOFF = 'woff';
    case WOFF2 = 'woff2';
    case EOT = 'eot';

    // Other
    case LOG = 'log';
    case BAK = 'bak';
    case TMP = 'tmp';
    case CACHE = 'cache';
    case LOCK = 'lock';
    case PID = 'pid';
    case SOCK = 'sock';
    case PATCH = 'patch';
    case DIFF = 'diff';
}
