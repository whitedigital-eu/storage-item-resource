<?php declare(strict_types = 1);

namespace WhiteDigital\StorageItemResource\DataFixture;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use WhiteDigital\StorageItemResource\Entity\StorageItem;

use function copy;
use function uniqid;

class StorageItemFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $data = [
            'text' => [
                'ext' => '.txt',
                'mime' => 'text/plain',
            ],
            'image' => [
                'ext' => '.png',
                'mime' => 'image/png',
            ],
        ];
        foreach ($data as $type => $item) {
            $templateFileName = __DIR__ . '/assets/storage_item-fixture.template' . $item['ext'];
            $fileName = __DIR__ . '/assets/storage_item-' . $type . $item['ext'];

            copy($templateFileName, $fileName);
            $file = new UploadedFile($fileName, 'storage_item-' . $type . $item['ext'], $item['mime'], test: true);

            $fixture = (new StorageItem())->setFile($file);
            $fixture->setTitle(uniqid());

            $manager->persist($fixture);
            $manager->flush();

            $this->addReference('wdFile_' . $type, $fixture);
        }
    }
}
