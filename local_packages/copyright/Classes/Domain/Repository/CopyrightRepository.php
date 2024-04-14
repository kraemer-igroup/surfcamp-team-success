<?php

declare(strict_types=1);

namespace Surfcamp\Copyright\Domain\Repository;

use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;

readonly class CopyrightRepository
{
    public function __construct(
        protected PageRepository $pageRepository,
        protected FileRepository $fileRepository,
        protected ConnectionPool $connectionPool
    )
    {
    }

    /**
     * @throws Exception
     */
    public function findBySite(Site $site): array
    {
        // first fetch all pages in a specific site
        $pageIds = $this->pageRepository->getDescendantPageIdsRecursive($site->getRootPageId(), 4);
        $pageIds[] = $site->getRootPageId();

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_file_reference');
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        $fileReferences = $queryBuilder
            ->select('*')
            ->from('sys_file_reference')
            ->where(
                $queryBuilder->expr()->in('pid', $queryBuilder->createNamedParameter($pageIds, Connection::PARAM_INT_ARRAY)),
            )
            ->orderBy('pid')
            ->executeQuery()
            ->fetchAllAssociative();

        $result = [];
        foreach ($fileReferences as $fileReference) {
            $fileObject = $this->fileRepository->findByUid($fileReference['uid_local']);
            if (array_key_exists($fileObject->getUid(), $result)) {
                $result[$fileObject->getUid()]['pages'][$fileReference['pid']] = $fileReference['pid'];
            } else {
                $result[$fileObject->getUid()] = [
                    'file' => $fileObject,
                    'pages' => [$fileReference['pid'] => $fileReference['pid']],
                ];
            }
        }
        return $result;
    }
}
