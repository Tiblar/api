<?php
namespace App\Service\Post;

use App\Entity\Media\AudioWave;
use Doctrine\ORM\EntityManagerInterface;
use maximal\audio\Waveform;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class WaveConverter {

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function file(UploadedFile $file) {
        $hash = hash_file('sha256', $file->getRealPath());

        $wave = $this->em->getRepository(AudioWave::class)->findOneBy([
            'hash' => $hash
        ]);

        if($wave INSTANCEOF AudioWave){
            return $wave->getData();
        }

        $fileName = $file->getRealPath();

        $ext = $file->guessClientExtension();
        $ext = str_replace('mpga', 'mp3', $ext);

        rename($fileName, $fileName .= "." . $ext);

        register_shutdown_function(function() use($fileName) {
            @unlink($fileName);
        });

        try{
            $waveform = new Waveform($fileName);
            $waveformData = $waveform->getWaveformData(640);
        }catch (\Exception $e){
            return null;
        }

        $waveData = [];
        foreach($waveformData['lines1'] as $line) {
            $waveData[] = round($line, 2);
        }

        $audioWave = new AudioWave();
        $audioWave->setHash($hash);
        $audioWave->setData($waveData);

        $this->em->persist($audioWave);
        $this->em->flush();

        return $waveData;
    }

    public function create(UploadedFile $file)
    {
        $wave = $this->em->getRepository(AudioWave::class)->findOneBy([
            'hash' => hash_file('sha256', $file->getRealPath())
        ]);

        if($wave INSTANCEOF AudioWave){
            return;
        }

        $audioWave = new AudioWave();
        $audioWave->setHash(sha1_file($file->getRealPath()));
        $audioWave->setData($this->file($file));

        $this->em->persist($audioWave);
        $this->em->flush();
    }
}
