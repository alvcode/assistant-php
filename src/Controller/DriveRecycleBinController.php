<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\NeedAuth;
use App\Entity\UserEntity;
use App\Infrastructure\Lang;
use App\Layer\Application\UseCase\DriveRecycleBin\DriveRBGetAllUseCase;
use App\Layer\Domain\Exception\AbstractLogicException;
use App\Response\DriveRecycleBin\DriveRBGetAllResponse;
use App\Security\BlockEvent\BlockEventService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class DriveRecycleBinController extends AbstractController
{
    public function __construct(
        private readonly BlockEventService $blockEventService,
    ) {}

    /**
     * Когда удаляем структуру, то смотрим все вложенные структуры и ищем их в корзине, если таковые имеются,
     * то удаляем их, т.к добавление в корзину родительской структуры перетирает их.
     * таким образом при восстановлении, мы удаляем только 1 запись корзины
     *
     * когда восстанавливаем...идем рекурсивно. если папка существует, то переименовываем добавляя restored_HaSh
     * если файл существует, то переименовываем добавляя restored_HaSh. в конце просто удаляем запись корзины и вся цепочка становится доступной
     *
     * тест кейсы:
     * 1. файл в корне. добавляем в корзину. восстанавливаем. файл появился
     * 2. файл в корне. добавляем в козину. загружаем такой же файл. восстанавливаем. файл появился с добавлением restored_HaSh
     * 3. такие же 2 кейса, но с папкой.
     * 4. папки 1/2 внутрь кладу 2 файла. добавляем папку 2 в корзину. создаю папку 2. кладу 2 таких же файла.
     *  добавляю в корзину папку 1. проверяю, что в корзине только папка 1. восстанавливаю.
     * 5. такой же кейс как 4 только не добавлять в корзину папку 1, а восстановить папку 2
     * 6. папки 1/2 внутрь кладу 2 файла. добавляю 2 файла в корзину. заливаю снова 2 таких же файла.
     *  восстанавливаю 2 файла из корзины
     *
     * Делаем метод для диска - upsert, который будет подменять файл и обновлять updated_at
     *
     */

    /**
     * @throws Exception
     */
    #[Route(path: '/api/drive-recycle-bin', name: 'drive_recycle_bin.get_all', methods: ['GET'])]
    #[NeedAuth]
    public function create(
        DriveRBGetAllUseCase $useCase,
    ): JsonResponse
    {
        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            return new JsonResponse(
                DriveRBGetAllResponse::fromDriveRBAggregates($useCase->handle($user->id)),
                Response::HTTP_OK
            );
        } catch (AbstractLogicException $e) {
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }
}
