<?php

namespace DivanteLtd\AdvancedSearchBundle\Controller;

use DivanteLtd\AdvancedSearchBundle\Model\SavedSearch;
use DivanteLtd\AdvancedSearchBundle\Service\FilterService as Service;
use Pimcore\Bundle\AdminBundle\Helper\QueryParams;
use Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Listing;
use Pimcore\Tool\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdminController
 *
 * @Route("/admin")
 * @SuppressWarnings(PHPMD)
 */
class AdminController extends \Pimcore\Bundle\AdminBundle\Controller\AdminController
{
    /**
     * @param Request $request
     * @param Service $service
     * @return JsonResponse
     * @throws \Exception
     * @Route("/get-fields")
     */
    public function getFieldsAction(Request $request, Service $service)
    {
        $type = strip_tags($request->get('type'));

        $allowInheritance = false;

        switch ($type) {
            case 'class':
                $classId = strip_tags($request->get('class_id'));
                $definition = DataObject\ClassDefinition::getById($classId);
                $allowInheritance = $definition->getAllowInherit();
                break;

            case 'fieldcollection':
                $key = strip_tags($request->get('key'));
                $definition = DataObject\Fieldcollection\Definition::getByKey($key);
                $allowInheritance = false;
                break;

            case 'objectbrick':
                $key = strip_tags($request->get('key'));
                $definition = DataObject\Objectbrick\Definition::getByKey($key);

                $classId = strip_tags($request->get('class_id'));
                $classDefinition = DataObject\ClassDefinition::getById($classId);
                $allowInheritance = $classDefinition->getAllowInherit();

                break;

            default:
                throw new \InvalidArgumentException("Invalid type '$type''");
        }

        $fieldSelection = $service->getFieldSelectionInformationForClassDefinition($definition, $allowInheritance);

        $fields = [];

        foreach ($fieldSelection as $entry) {
            $fields[] = $entry->toArray();
        }

        return $this->adminJson(['data' => $fields]);
    }

    /**
     * @param Request $request
     * @param Service $service
     *
     * @return JsonResponse
     * @Route("/get-field-options")
     * @throws \Exception
     *
     */
    public function getFieldOptionsAction(Request $request, Service $service)
    {
        $classId = (int)$request->get('classId');
        $fieldName = $request->get('fieldName');

        return $this->adminJson([
            'data' => $service->getFieldOptions($classId, $fieldName),
        ]);
    }

    /**
     * @param Request $request
     * @param Service $service
     *
     * @return JsonResponse|Response
     * @Route("/grid-proxy")
     * @throws \Exception
     *
     */
    public function gridProxyAction(Request $request, Service $service)
    {
        $requestedLanguage = $request->get('language');

        if ($requestedLanguage) {
            if ($requestedLanguage != 'default') {
                $request->setLocale($requestedLanguage);
            }
        } else {
            $requestedLanguage = $request->getLocale();
        }

        if ($request->get('data')) {
            return $this->forward(
                'PimcoreAdminBundle:Admin/DataObject/DataObject:gridProxy',
                [],
                $request->query->all()
            );
        } else {
            $class = DataObject\ClassDefinition::getById($request->get('classId'));
            $className = $class->getName();

            $fields = [];
            if ($request->get('fields')) {
                $fields = $request->get('fields');
            }

            $start = 0;
            $limit = 20;
            $sortBy = 'o_id';
            $sortDirection = 'ASC';

            if ($request->get('limit')) {
                $limit = $request->get('limit');
            }
            if ($request->get('start')) {
                $start = $request->get('start');
            }

            if ($request->get('sort')) {
                $sort = json_decode($request->get('sort'))[0];

                $sortBy = $sort->property;
                $sortDirection = $sort->direction;

                $colMappings = [
                    'key' => 'o_key',
                    'fullpath' => ['o_path', 'o_key'],
                    'id' => 'o_id',
                    'published' => 'o_published',
                    'modificationDate' => 'o_modificationDate',
                    'creationDate' => 'o_creationDate'
                ];

                if (array_key_exists($sortBy, $colMappings)) {
                    $sortBy = $colMappings[$sortBy];
                }
            }

            $listClass = '\\Pimcore\\Model\\DataObject\\' . ucfirst($className) . '\\Listing';

            $data = json_decode($request->get('filter'), true);

            /**
             * @var $list Listing
             */
            $list = new $listClass();
            $list->setObjectTypes(['object', 'folder', 'variant']);
            $list = $service->doFilter($list, $data['conditions'] ?? []);

            $list->setOrderKey($sortBy);
            $list->setOrder($sortDirection);
            $list->setOffset($start);
            $list->setLimit($limit);
            $list->setUnpublished(true);

            $list->load();

            $objects = [];

            foreach ($list->getObjects() as $object) {
                $o = DataObject\Service::gridObjectData($object, $fields, $requestedLanguage);
                $objects[] = $o;
            }

            return $this->adminJson(['data' => $objects, 'success' => true, 'total' => $list->getTotalCount()]);
        }
    }

    /**
     * //     *
     * @param Request $request
     * //     * @Route("/get-batch-jobs")
     * //
     * @param Service $service
     * @return JsonResponse
     * @throws \Exception
     */
    public function getBatchJobsAction(Request $request, Service $service)
    {
        if ($request->get('language')) {
            $request->setLocale($request->get('language'));
        }

        $class = DataObject\ClassDefinition::getById($request->get('classId'));

        $className = $class->getName();
        $listClass = '\\Pimcore\\Model\\DataObject\\' . ucfirst($className) . '\\Listing';
        $list = new $listClass();

        $data = json_decode($request->get('filter'), true);
        $results = $service->doFilter($list, $data['conditions']);

        $ids = $results->loadIdList();

        $list->setObjectTypes(['object', 'folder', 'variant']);
        $list->setCondition('o_id IN (' . implode(',', $ids) . ')');
        $list->setOrderKey(' FIELD(o_id, ' . implode(',', $ids) . ')', false);

        if ($request->get('objecttype')) {
            $list->setObjectTypes([$request->get('objecttype')]);
        }

        $jobs = $list->loadIdList();

        return $this->adminJson(['success' => true, 'jobs' => $jobs]);
    }

    /**
     * @param Request $request
     * @param Service $service
     *
     * @return JsonResponse
     * @throws \Exception
     * @Route("/get-export-jobs")
     */
    public function getExportJobsAction(Request $request, Service $service)
    {
        if ($request->get('language')) {
            $request->setLocale($request->get('language'));
        }

        $ids = $request->get('ids');

        if (!$ids) {
            $data = json_decode($request->get('filter'), true);

            $class = DataObject\ClassDefinition::getById($data['classId']);
            $className = $class->getName();

            $listClass = '\\Pimcore\\Model\\DataObject\\' . ucfirst($className) . '\\Listing';

            /**
             * @var $list Listing
             */
            $list = new $listClass();
            $list->setObjectTypes(['object', 'folder', 'variant']);

            $results = $service->doFilter(
                $list,
                $data['conditions']
            );

            $ids = $results->loadIdList();
        }

        $jobs = array_chunk($ids, 20);

        $fileHandle = uniqid('export-');
        file_put_contents($this->getCsvFile($fileHandle), '');

        return $this->adminJson(['success' => true, 'jobs' => $jobs, 'fileHandle' => $fileHandle]);
    }

    /**
     * @param string $fileHandle
     *
     * @return string
     */
    protected function getCsvFile($fileHandle)
    {
        return PIMCORE_SYSTEM_TEMP_DIRECTORY . '/' . $fileHandle . '.csv';
    }

    /**
     * @param Request $request
     * @Route("/get-users")
     * @return JsonResponse
     */
    public function getUsersAction(Request $request)
    {
        // get available user
        $list = new \Pimcore\Model\User\Listing();

        $list->load();
        $userList = $list->getUsers();

        $users = [];
        foreach ($userList as $user) {
            $users[] = [
                'id' => $user->getId(),
                'label' => $user->getName(),
            ];
        }

        return $this->adminJson(['success' => true, 'total' => count($users), 'data' => $users]);
    }

    /**
     * @param Request $request
     * @Route("/save")
     * @return JsonResponse
     */
    public function saveAction(Request $request)
    {
        $data = $request->get('data');
        $data = json_decode($data);

        $id = ((int)($request->get('id')));
        if ($id) {
            $savedSearch = SavedSearch::getById($id);
        } else {
            $savedSearch = new SavedSearch();
            $savedSearch->setOwner($this->getAdminUser());
        }

        $savedSearch->setName($data->settings->name);
        $savedSearch->setDescription($data->settings->description);
        $savedSearch->setCategory($data->settings->category);
        $savedSearch->setSharedUserIds($data->settings->shared_users);

        $config = ['classId' => $data->classId, 'gridConfig' => $data->gridConfig, 'conditions' => $data->conditions];
        $savedSearch->setConfig(json_encode($config));

        $savedSearch->save();

        return $this->adminJson(['success' => true, 'id' => $savedSearch->getId()]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     * @Route("/load-search")
     */
    public function loadSearchAction(Request $request)
    {
        $id = (int)($request->get('id'));
        $savedSearch = SavedSearch::getById($id);
        if ($savedSearch) {
            $config = json_decode($savedSearch->getConfig(), true);
            $classDefinition = DataObject\ClassDefinition::getById($config['classId']);

            if (!empty($config['gridConfig']['columns'])) {
                $helperColumns = [];

                foreach ($config['gridConfig']['columns'] as &$column) {
                    if (!$column['isOperator']) {
                        $fieldDefinition = $classDefinition->getFieldDefinition($column['key']);
                        if ($fieldDefinition) {
                            $width = $column['layout']['width'] ?? null;
                            $column['layout'] = json_decode(json_encode($fieldDefinition), true);
                            if ($width) {
                                $column['layout']['width'] = $width;
                            }
                        }
                    }

                    if (!DataObject\Service::isHelperGridColumnConfig($column['key'])) {
                        continue;
                    }

                    // columnconfig has to be a stdclass
                    $helperColumns[$column['key']] = json_decode(json_encode($column));
                }

                // store the saved search columns in the session, otherwise they won't work
                Session::useSession(function (AttributeBagInterface $session) use ($helperColumns): void {
                    $existingColumns = $session->get('helpercolumns', []);
                    $helperColumns = array_merge($existingColumns, $helperColumns);
                    $session->set('helpercolumns', $helperColumns);
                }, 'pimcore_gridconfig');
            }

            return $this->adminJson([
                'id' => $savedSearch->getId(),
                'classId' => $config['classId'],
                'settings' => [
                    'name' => $savedSearch->getName(),
                    'description' => $savedSearch->getDescription(),
                    'category' => $savedSearch->getCategory(),
                    'sharedUserIds' => $savedSearch->getSharedUserIds(),
                    'isOwner' => $savedSearch->getOwnerId() == $this->getAdminUser()->getId(),
                    'hasShortCut' => $savedSearch->isInShortCutsForUser($this->getAdminUser()),
                ],
                'conditions' => $config['conditions'],
                'gridConfig' => $config['gridConfig'],
            ]);
        } else {
            return $this->adminJson(['success' => false, 'message' => "Saved Search with $id not found."]);
        }
    }

    /**
     * @param Request $request
     * @Route("/find")
     * @return JsonResponse
     */
    public function findAction(Request $request)
    {
        $user = $this->getAdminUser();

        $query = $request->get('query');
        if ($query == '*') {
            $query = '';
        }

        $query = str_replace('%', '*', $query);

        $offset = (int)($request->get('start'));
        $limit = (int)($request->get('limit'));

        $offset = $offset ? $offset : 0;
        $limit = $limit ? $limit : 50;

        $searcherList = new SavedSearch\Listing();
        $conditionParts = [];
        $conditionParams = [];

        //filter for current user
        $conditionParts[] = '(ownerId = ? OR sharedUserIds LIKE ?)';
        $conditionParams[] = $user->getId();
        $conditionParams[] = '%,' . $user->getId() . ',%';

        //filter for query
        if (!empty($query)) {
            $conditionParts[] = '(name LIKE ? OR description LIKE ? OR category LIKE ?)';
            $conditionParams[] = '%' . $query . '%';
            $conditionParams[] = '%' . $query . '%';
            $conditionParams[] = '%' . $query . '%';
        }

        if (count($conditionParts) > 0) {
            $condition = implode(' AND ', $conditionParts);
            $searcherList->setCondition($condition, $conditionParams);
        }

        $searcherList->setOffset($offset);
        $searcherList->setLimit($limit);

        $sortingSettings = QueryParams::extractSortingSettings(
            array_merge($request->request->all(), $request->query->all())
        );
        if ($sortingSettings['orderKey']) {
            $searcherList->setOrderKey($sortingSettings['orderKey']);
        }
        if ($sortingSettings['order']) {
            $searcherList->setOrder($sortingSettings['order']);
        }

        $results = []; //$searcherList->load();
        foreach ($searcherList->load() as $result) {
            $results[] = [
                'id' => $result->getId(),
                'name' => $result->getName(),
                'description' => $result->getDescription(),
                'category' => $result->getCategory(),
                'owner' => $result->getOwner() ?
                    $result->getOwner()->getUsername() . ' (' .
                    $result->getOwner()->getFirstname() . ' ' . $result->getOwner()->getLastName() . ')' :
                    '',
                'ownerId' => $result->getOwnerId(),
            ];
        }

        // only get the real total-count when the limit parameter is given otherwise use the default limit
        if ($request->get('limit')) {
            $totalMatches = $searcherList->getTotalCount();
        } else {
            $totalMatches = count($results);
        }

        return $this->adminJson(['data' => $results, 'success' => true, 'total' => $totalMatches]);
    }

    /**
     * @param Request $request
     * @Route("/load-short-cuts")
     * @return JsonResponse
     */
    public function loadShortCutsAction(Request $request)
    {
        $list = new SavedSearch\Listing();
        $list->setCondition(
            '(ownerId = ? OR sharedUserIds LIKE ?) AND shortCutUserIds LIKE ?',
            [
                $this->getAdminUser()->getId(),
                '%,' . $this->getAdminUser()->getId() . ',%',
                '%,' . $this->getAdminUser()->getId() . ',%'
            ]
        );
        $list->load();
        $entries = [];
        foreach ($list->getSavedSearches() as $entry) {
            $entries[] = [
                'id' => $entry->getId(),
                'name' => $entry->getName(),
            ];
        }

        return $this->adminJson(['entries' => $entries]);
    }

    /**
     * @param Request $request
     * @Route("/toggle-short-cut")
     * @return JsonResponse
     */
    public function toggleShortCutAction(Request $request)
    {
        $id = (int)($request->get('id'));
        $savedSearch = SavedSearch::getById($id);
        if ($savedSearch) {
            $user = $this->getAdminUser();
            if ($savedSearch->isInShortCutsForUser($user)) {
                $savedSearch->removeShortCutForUser($user);
            } else {
                $savedSearch->addShortCutForUser($user);
            }
            $savedSearch->save();

            return $this->adminJson(['success' => 'true', 'hasShortCut' => $savedSearch->isInShortCutsForUser($user)]);
        } else {
            return $this->adminJson(['success' => 'false']);
        }
    }

    /**
     * @param Request $request
     * @Route("/delete")
     * @return JsonResponse
     */
    public function deleteAction(Request $request)
    {
        $id = (int)($request->get('id'));

        $savedSearch = SavedSearch::getById($id);

        if ($savedSearch) {
            $savedSearch->delete();

            return $this->adminJson(['success' => true, 'id' => $savedSearch->getId()]);
        } else {
            return $this->adminJson(['success' => false, 'message' => "Saved Search with $id not found."]);
        }
    }
}
