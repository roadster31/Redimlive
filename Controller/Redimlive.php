<?php
/*************************************************************************************/
/*                                                                                   */
/*      Copyright (c) Franck Allimant, CQFDev                                        */
/*      email : thelia@cqfdev.fr                                                     */
/*      web : http://www.cqfdev.fr                                                   */
/*                                                                                   */
/*************************************************************************************/

/**
 * Created by Franck Allimant, CQFDev <franck@cqfdev.fr>
 * Date: 18/01/2016 23:20
 */
namespace Redimlive\Controller;

use Propel\Runtime\ActiveQuery\ModelCriteria;
use Symfony\Component\HttpFoundation\Response;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\Event\Image\ImageEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Log\Tlog;
use Thelia\Model\ConfigQuery;

class Redimlive extends BaseFrontController
{
    public function redimlive($type, $id, $offset, $width, $height, $resizeMode = 'none')
    {
        $object = ucfirst($type);

        $queryClass   = sprintf("Thelia\\Model\\%sImageQuery", $object);
        $filterMethod = sprintf("filterBy%sId", $object);

        // xxxImageQuery::create()
        $method = new \ReflectionMethod($queryClass, 'create');
        /** @var ModelCriteria $search */
        $search = $method->invoke(null); // Static !

        // $query->filterByXXX(id)
        $method = new \ReflectionMethod($queryClass, $filterMethod);
        $method->invoke($search, $id);

        if (null !== $image = $search->offset(max(0, $offset - 1))->limit(1)->findOne()) {
            switch ($resizeMode) {
                case 'crop':
                    $resizeMode = \Thelia\Action\Image::EXACT_RATIO_WITH_CROP;
                    break;

                case 'borders':
                    $resizeMode = \Thelia\Action\Image::EXACT_RATIO_WITH_BORDERS;
                    break;

                case 'none':
                default:
                    $resizeMode = \Thelia\Action\Image::KEEP_IMAGE_RATIO;

            }

            $baseSourceFilePath = ConfigQuery::read('images_library_path');
            if ($baseSourceFilePath === null) {
                $baseSourceFilePath = THELIA_LOCAL_DIR . 'media' . DS . 'images';
            } else {
                $baseSourceFilePath = THELIA_ROOT . $baseSourceFilePath;
            }

            // Put source image file path
            $sourceFilePath = sprintf(
                '%s/%s/%s',
                $baseSourceFilePath,
                $object,
                $image->getFile()
            );

            // Create image processing event
            $event = new ImageEvent($this->getRequest());

            $event
                ->setSourceFilepath($sourceFilePath)
                ->setCacheSubdirectory($object)
                ->setWidth($width)
                ->setHeight($height)
                ->setResizeMode($resizeMode)
            ;

            try {
                // Dispatch image processing event
                $this->getDispatcher()->dispatch(TheliaEvents::IMAGE_PROCESS, $event);

                return Response::create(
                    file_get_contents($event->getCacheFilepath()),
                    200,
                    array(
                        'Content-type' => image_type_to_mime_type(exif_imagetype($event->getCacheFilepath())),
                    )
                );
            } catch (\Exception $ex) {
                Tlog::getInstance()->addError(sprintf("Failed to process image: %s", $ex->getMessage()));

                return new Response("", 500);
            }
        } else {
            $this->pageNotFound();
        }
    }
}
