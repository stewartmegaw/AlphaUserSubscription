<?php

/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace AlphaUserSubscription\Entity;

use AlphaUser\Entity\AlphaUserBase;
use AlphaSubscription\Entity\SubscriptionInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * An example of how to implement a role aware user entity.
 *
 * @ORM\Entity
 * @ORM\Table(name="users_sub")
 *
 * @author Tom Oram <tom@scl.co.uk>
 */
class User extends AlphaUserBase implements SubscriptionInterface {

    public function getSubscribedFiltersIds($addZeroIfEmpty = true) {
        $filters_tmp = array();

        $subscriptionColumns = $this->getEntityManager()->getRepository('Alpha\Entity\AlphaStructure')
                ->findOneBy(['entityName' => 'AlphaSubscription\Entity\Subscription'])
                ->getColumns();

        $statusField = null;

        foreach ($subscriptionColumns as $column) {
            if ($column->getColumnName() == 'status')
                $statusField = $column->getName();
        }

        $subscriptions = $this->getEntityManager()
                ->getRepository('AlphaSubscription\Entity\Subscription')
                ->findBy(array('user' => $this, $statusField => \AlphaSubscription\Entity\Subscription::STATUS_ENABLED));

        foreach ($subscriptions as $subscription) {
            if ($subscription->getStatus() == \AlphaSubscription\Entity\Subscription::STATUS_ENABLED) {
                foreach ($subscription->getSubscriptionItem()->getFilters() as $filter) {
                    $filters_tmp[] = $filter->getId();
                }
            }
        }

        $filters = array_unique($filters_tmp);

        if (empty($filters) && $addZeroIfEmpty)
            $filters[] = 0;

        return $filters;
    }

    public function getSubscriptions() {
        $subscriptions = null;

        $subscriptionColumns = $this->getEntityManager()->getRepository('Alpha\Entity\AlphaStructure')
                ->findOneBy(['entityName' => 'AlphaSubscription\Entity\Subscription'])
                ->getColumns();

        $statusField = 'status';

        foreach ($subscriptionColumns as $column) {
            if ($column->getColumnName() == 'status')
                $statusField = $column->getName();
        }

        $subscriptions = $this->getEntityManager()
                ->getRepository('AlphaSubscription\Entity\Subscription')
                ->findBy(array('user' => $this, $statusField => \AlphaSubscription\Entity\Subscription::STATUS_ENABLED));

        return $subscriptions;
    }

}
