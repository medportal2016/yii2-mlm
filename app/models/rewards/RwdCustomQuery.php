<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  31/05/2017 13:26
 */

namespace dlds\mlm\app\models\rewards;

use dlds\mlm\app\models\rewards\RwdBasic;
use dlds\mlm\app\models\rewards\RwdCustom;
use dlds\mlm\app\models\rewards\RwdExtra;
use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use dlds\mlm\kernel\interfaces\queries\MlmRewardQueryInterface;
use dlds\mlm\kernel\traits\MlmParticipantTrait;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class ParticipantQuery
 * @package dlds\mlm\app\models
 * @see RwdCustom
 */
class RwdCustomQuery extends ActiveQuery implements MlmRewardQueryInterface
{
    /**
     * @inheritdoc
     * @param MlmParticipantInterface $participant
     * @return $this
     */
    public function __mlmProfiteer(MlmParticipantInterface $participant)
    {
        $this->andWhere(['usr_rewarded_id' => $participant->__mlmPrimaryKey()]);

        return $this;
    }

    /**
     * @inheritdoc
     * @param int $id
     * @param null $type
     * @return $this
     */
    public function __mlmSource($id, $type = null)
    {
        $this->andWhere(['subject_id' => $id]);

        if ($type) {
            $this->andWhere(['subject_type' => $type]);
        }

        return $this;
    }

    /**
     * @inheritdoc
     * @param bool $state
     * @return $this
     */
    public function __mlmPending($state = true)
    {
        $operand = $state ? '=' : '<>';

        $this->andWhere([$operand, 'status', 'pending']);

        return $this;
    }

    /**
     * @inheritdoc
     * @param bool $state
     * @return $this
     */
    public function __mlmApproved($state = true)
    {
        $operand = $state ? '=' : '<>';

        $this->andWhere([$operand, 'status', 'approved']);

        return $this;
    }

    /**
     * @inheritdoc
     * @param bool $state
     * @return $this
     */
    public function __mlmDenied($state = true)
    {
        $operand = $state ? '=' : '<>';

        $this->andWhere([$operand, 'status', 'denied']);

        return $this;
    }

    /**
     * @inheritdoc
     * @param bool $state
     * @return $this
     */
    public function __mlmPaid($state = true)
    {
        $operand = $state ? '=' : '<>';

        $this->andWhere([$operand, 'status', 'paid']);

        return $this;
    }

    /**
     * @inheritdoc
     * @param bool $state
     * @return $this
     */
    public function __mlmLocked($state = true)
    {
        $operand = $state ? '=' : '<>';

        $this->andWhere([$operand, 'status', 1]);

        return $this;
    }

    /**
     * @inheritdoc
     * @param bool $state
     * @return $this
     */
    public function __mlmFinal($state = true)
    {
        $operand = $state ? '=' : '<>';

        $this->andWhere([$operand, 'status', 1]);

        return $this;
    }

    /**
     * @inheritdoc
     * @param int $value
     * @param string $operator
     * @return $this
     */
    public function __mlmAge($value, $operator = self::OP_OLDER)
    {
        $birth = time() - $value;

        $this->andWhere([$operator, 'created_at', $birth]);

        return $this;
    }

    /**
     * @inheritdoc
     * @param integer|null $delay
     * @return $this
     */
    public function __mlmExpectingApproval($delay = null)
    {
        $this->__mlmAge($delay);

        $this->joinWith(['usrRewarded' => function ($q) {
            $q->__mlmEligibleToCustomRewards(true);
        }]);

        $this->__mlmPending(true);

        return $this;
    }

    /**
     * @inheritdoc
     * @param integer|null $delay
     * @return $this
     */
    public function __mlmExpectingDeny($delay = null)
    {
        $this->__mlmAge($delay);

        $this->joinWith(['usrRewarded' => function ($q) {
            $q->__mlmEligibleToCustomRewards(false);
        }]);

        $this->__mlmPending(true);

        return $this;
    }
}
