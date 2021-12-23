<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2021 Vitor Mattos <vitor@php.rio>
 *
 * @author Vitor Mattos <vitor@php.rio>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Talk\Controller;

use OCA\Talk\Chat\CommentsManager;
use OCA\Talk\Chat\ReactionManager;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Comments\NotFoundException;
use OCP\IRequest;

class ReactionController extends AEnvironmentAwareController {
	/** @var ITimeFactory */
	protected $timeFactory;
	/** @var CommentsManager */
	private $commentsManager;
	/** @var ReactionManager */
	private $reactionManager;

	public function __construct(string $appName,
								IRequest $request,
								ITimeFactory $timeFactory,
								CommentsManager $commentsManager,
								ReactionManager $reactionManager) {
		parent::__construct($appName, $request);

		$this->timeFactory = $timeFactory;
		$this->commentsManager = $commentsManager;
		$this->reactionManager = $reactionManager;
	}

	/**
	 * @NoAdminRequired
	 * @RequireParticipant
	 * @RequireReadWriteConversation
	 * @RequireModeratorOrNoLobby
	 *
	 * @param int $messageId for reaction
	 * @param string $emoji the reaction emoji
	 * @return DataResponse
	 */
	public function react(int $messageId, string $emoji): DataResponse {
		$participant = $this->getParticipant();
		try {
			// Verify if messageId is of room
			$this->commentsManager->getComment($this->getRoom(), (string) $messageId);
		} catch (NotFoundException $e) {
			return new DataResponse([], Http::STATUS_NOT_FOUND);
		}

		try {
			// Verify already reacted whith the same reaction
			$this->commentsManager->getReactionComment(
				$messageId,
				$participant->getAttendee()->getActorType(),
				$participant->getAttendee()->getActorId(),
				$emoji
			);
			return new DataResponse([], Http::STATUS_CONFLICT);
		} catch (NotFoundException $e) {
		}

		try {
			$this->reactionManager->addReactionMessage($this->getRoom(), $participant, $messageId, $emoji);
		} catch (\Exception $e) {
			return new DataResponse([], Http::STATUS_BAD_REQUEST);
		}

		return new DataResponse([], Http::STATUS_CREATED);
	}

	/**
	 * @NoAdminRequired
	 * @RequireParticipant
	 * @RequireReadWriteConversation
	 * @RequireModeratorOrNoLobby
	 *
	 * @param int $messageId for reaction
	 * @param string $emoji the reaction emoji
	 * @return DataResponse
	 */
	public function delete(int $messageId, string $emoji): DataResponse {
		$participant = $this->getParticipant();

		try {
			$this->reactionManager->deleteReactionMessage(
				$participant,
				$messageId,
				$emoji,
				$this->timeFactory->getDateTime()
			);
		} catch (NotFoundException $e) {
			return new DataResponse([], Http::STATUS_NOT_FOUND);
		} catch (\Exception $e) {
			return new DataResponse([], Http::STATUS_BAD_REQUEST);
		}

		return new DataResponse([], Http::STATUS_CREATED);
	}
}
