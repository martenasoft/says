<?php

namespace App\Service;

use App\Entity\Page;
use App\Helper\StringHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class SaveImageService
{
    public function __construct(
        private ParameterBagInterface $parameterBag,
        private EntityManagerInterface $entityManager
    )
    {
    }

    public function upload(Form $form, Page $data): void
    {
        $image = $form->get('image')->getData();
        if (!$image) {
            return;
        }

        $originalFilename =  uniqid() .'-'.pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $originalFilename = StringHelper::slug($originalFilename);
        $newFilename =  $originalFilename.'.'.$image->guessExtension();
        $path = $this->parameterBag->get('image_path');
        $originPath = $path.'/'.Page::PATH[Page::ORIGIN_IMAGE_TYPE];

        try {
            if (!empty($data->getImage()) && file_exists($originPath .'/'.$data->getImage())) {
                unlink($originPath .'/'.$data->getImage());
            }
            $image->move($originPath,  $newFilename);

            foreach (Page::IMAGE_TYPES as $key => $item) {
                if ($key == Page::ORIGIN_IMAGE_TYPE) {
                    continue;
                }

                $resizedPath = $path.'/'.Page::PATH[$key];

                if (!empty($data->getImage()) && file_exists($resizedPath .'/'.$data->getImage())) {
                    unlink($resizedPath .'/'.$data->getImage());
                }

                if (!file_exists($resizedPath)) {
                    mkdir($resizedPath, 0777);
                }

                $imageOptimizer = new ImageOptimizer(
                    Page::SIZES[$key]['width'],
                    Page::SIZES[$key]['height']
                );
                copy($originPath.'/'.$newFilename, $resizedPath.'/'.$newFilename);
                $imageOptimizer->resize($resizedPath.'/'.$newFilename);
            }

            $data->setImage($newFilename);


        } catch (FileException $e) {
            throw $e;
        }
        $data->setPath($path);
    }

}