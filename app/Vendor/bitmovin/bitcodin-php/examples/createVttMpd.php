<?php

use bitcodin\Bitcodin;
use bitcodin\VideoStreamConfig;
use bitcodin\AudioStreamConfig;
use bitcodin\Job;
use bitcodin\JobConfig;
use bitcodin\Input;
use bitcodin\HttpInputConfig;
use bitcodin\EncodingProfile;
use bitcodin\EncodingProfileConfig;
use bitcodin\ManifestTypes;
use bitcodin\VttMpdConfig;
use bitcodin\VttMpd;
use bitcodin\VttSubtitle;
use bitcodin\Output;
use bitcodin\FtpOutputConfig;

require_once __DIR__.'/../vendor/autoload.php';

///* CONFIGURATION */
Bitcodin::setApiToken('YOUR API KEY'); // Your can find your api key in the settings menu. Your account (right corner) -> Settings -> API

$inputConfig = new HttpInputConfig();
$inputConfig->url = 'http://eu-storage.bitcodin.com/inputs/Sintel.2010.720p.mkv';
$input = Input::create($inputConfig);

$encodingProfileConfig = new EncodingProfileConfig();
$encodingProfileConfig->name = 'MyApiTestEncodingProfile';

/* CREATE VIDEO STREAM CONFIGS */
$videoStreamConfig1 = new VideoStreamConfig();
$videoStreamConfig1->bitrate = 4800000;
$videoStreamConfig1->height = 1080;
$videoStreamConfig1->width = 1920;
$encodingProfileConfig->videoStreamConfigs[] = $videoStreamConfig1;

$videoStreamConfig2 = new VideoStreamConfig();
$videoStreamConfig2->bitrate = 2400000;
$videoStreamConfig2->height = 720;
$videoStreamConfig2->width = 1280;
$encodingProfileConfig->videoStreamConfigs[] = $videoStreamConfig2;

$videoStreamConfig3 = new VideoStreamConfig();
$videoStreamConfig3->bitrate = 1200000;
$videoStreamConfig3->height = 480;
$videoStreamConfig3->width = 854;
$encodingProfileConfig->videoStreamConfigs[] = $videoStreamConfig3;

/* CREATE AUDIO STREAM CONFIGS */
$audioStreamConfig = new AudioStreamConfig();
$audioStreamConfig->bitrate = 128000;
$encodingProfileConfig->audioStreamConfigs[] = $audioStreamConfig;

/* CREATE ENCODING PROFILE */
$encodingProfile = EncodingProfile::create($encodingProfileConfig);

$jobConfig = new JobConfig();
$jobConfig->encodingProfile = $encodingProfile;
$jobConfig->input = $input;
$jobConfig->manifestTypes[] = ManifestTypes::M3U8;
$jobConfig->manifestTypes[] = ManifestTypes::MPD;

/* CREATE JOB */
$job = Job::create($jobConfig);

/* WAIT UNTIL JOB IS FINISHED */
do{
    $job->update();
    sleep(1);
} while($job->status != Job::STATUS_FINISHED && $job->status != Job::STATUS_ERROR);


/* CREATE MPD WITH YOUR VTT SUBTITLES */
$eng_sub = new VttSubtitle();
$eng_sub->langLong = "English";
$eng_sub->langShort = "eng";
$eng_sub->url = "http://url.to/your/eng.vtt";

$de_sub = new VttSubtitle();
$de_sub->langLong = "Deutsch";
$de_sub->langShort = "de";
$de_sub->url = "http://url.to/your/de.vtt";

$subtitles = array($eng_sub, $de_sub);

$vttMpdConfig = new VttMpdConfig();
$vttMpdConfig->jobId = $job->jobId;
$vttMpdConfig->subtitles = $subtitles;

$mpdRes = VttMpd::create($vttMpdConfig);
echo "URL OF VTT MPD: ".$mpdRes->mpdUrl."\n";