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

namespace OCA\Talk\Chat;

use OCA\Talk\Participant;
use OCA\Talk\Room;
use OCP\Comments\IComment;
use OCP\Comments\ICommentsManager;

class ReactionManager {
	/** @var ICommentsManager|CommentsManager */
	private $commentsManager;

	public function __construct(CommentsManager $commentsManager) {
		$this->commentsManager = $commentsManager;
	}

	public function addReactionMessage(Room $chat, Participant $participant, int $messageId, string $emoji): IComment {
		$comment = $this->commentsManager->create(
			$participant->getAttendee()->getActorType(),
			$participant->getAttendee()->getActorId(),
			'chat',
			(string) $chat->getId()
		);
		$comment->setParentId((string) $messageId);
		$comment->setMessage($emoji);
		$comment->setVerb('reaction');
		$this->commentsManager->save($comment);
		return $comment;
	}

	public function deleteReactionMessage(Participant $participant, int $messageId, string $emoji, \DateTime $deletionTime): IComment {
		$comment = $this->commentsManager->getReactionComment(
			$messageId,
			$participant->getAttendee()->getActorType(),
			$participant->getAttendee()->getActorId(),
			$emoji
		);
		$comment->setMessage(
			json_encode([
				'deleted_by_type' => $participant->getAttendee()->getActorType(),
				'deleted_by_id' => $participant->getAttendee()->getActorId(),
				'deleted_on' => $deletionTime->getTimestamp(),
			])
		);
		$comment->setVerb('reaction_delete');
		$this->commentsManager->save($comment);
		return $comment;
	}
}
