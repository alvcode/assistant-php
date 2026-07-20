<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\NeedAuth;
use App\Entity\UserEntity;
use App\Infrastructure\FormatDict;
use App\Request\LongPolling\PoolRequest;
use App\Response\LongPolling\PoolResponse;
use App\Security\BlockEvent\BlockEventService;
use App\Security\BlockEvent\BlockEventTypeEnum;
use DateMalformedStringException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class LongPollingController extends AbstractController
{
    private const int DURATION_SECONDS = 25;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BlockEventService $blockEventService,
    ) {}

    /**
     * @throws Exception
     */
    #[Route(path: '/api/long-polling/pool', name: 'long_polling.pool', methods: ['GET'])]
    #[NeedAuth]
    public function pool(Request $request, PoolRequest $requestModel): JsonResponse
    {
        if (!$requestModel->populateByQueryParams($request)->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        /** @var UserEntity $user */
        $user = $this->getUser();

        $endTime = strtotime(sprintf('+%d seconds', self::DURATION_SECONDS));
        $res = [];
        while ($endTime > time()) {
            $res = $this->getEvents($user->id, $requestModel->getLastEventId());
            if (!empty($res)) {
                return new JsonResponse($res, Response::HTTP_OK);
            }
            usleep(500_000);
        }
        return new JsonResponse($res, Response::HTTP_OK);
    }

    /**
     * @return PoolResponse[]
     * @throws \Doctrine\DBAL\Exception
     * @throws DateMalformedStringException
     */
    private function getEvents(int $userId, ?int $lastEventId): array
    {
        $query = "select * from long_polling_events where user_id = :user_id and expired_at > :now";
        $params = [
            'user_id' => $userId,
            'now' => (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
                ->format(FormatDict::DB_DATETIME),
        ];

        if (!is_null($lastEventId)) {
            $query .= " and id > :last_event_id";
            $params['last_event_id'] = $lastEventId;
        }

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, $params);
        $result = [];
        foreach ($stmt->fetchAllAssociative() as $raw) {
            $result[] = new PoolResponse(
                id: $raw['id'],
                eventKey: $raw['event_key'],
                payload: $raw['payload'] ? json_decode($raw['payload'], true) : [],
            );
        }
        return $result;
    }
}
