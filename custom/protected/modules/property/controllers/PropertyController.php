<?php

/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/


/**
 * @package application.modules.property.controllers
 */
class PropertyController extends x2base
{

    public $modelClass = 'Property';

    public function accessRules()
    {
        return array(
            array('allow',
                'actions' => array('getItems'),
                'users' => array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('index', 'view', 'create', 'update', 'search', 'addUser', 'removeUser',
                    'addNote', 'deleteNote', 'saveChanges', 'delete', 'shareProperty', 'inlineEmail', 'qtip', 'createList',
                    'createListFromSelection',
                    'updateList',
                    'addToList', 'removeFromList',
                    'deleteList',),
                'users' => array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array('admin', 'testScalability'),
                'users' => array('admin'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), array(
            'MobileControllerBehavior' => array(
                'class' =>
                    'application.modules.mobile.components.behaviors.MobileControllerBehavior'
            ),
            'MobileActionHistoryBehavior' => array(
                'class' =>
                    'application.modules.mobile.components.behaviors.MobileActionHistoryBehavior'
            ),
            'QuickCreateRelationshipBehavior' => array(
                'class' => 'QuickCreateRelationshipBehavior',
                'attributesOfNewRecordToUpdate' => array(
                    'Contacts' => array(
                        'nameId' => 'company',
                        'website' => 'website',
                        'phone' => 'phone',
                    ),
                    'Opportunity' => array(
                        'propertyName' => 'id',
                    )
                )
            ),
        ));
    }

    public function actions()
    {
        return array_merge(parent::actions(), array(
            'inlineEmail' => array(
                'class' => 'InlineEmailAction',
            ),
            'propertyReport' => array(
                'class' => 'PropertyReportAction',
            ),
            'exportPropertyReport' => array(
                'class' => 'ExportPropertyReportAction',
            ),
            'propertyCampaign' => array(
                'class' => 'PropertyCampaignAction',
            ),
        ));
    }

    public function actionGetItems($term)
    {
        LinkableBehavior::getItems($term);
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id)
    {
        $model = $this->loadModel($id);
        if (!parent::checkPermissions($model, 'view'))
            $this->denied();

        // add property to user's recent item list
        User::addRecentItem('a', $id, Yii::app()->user->getId());
        if ($model->checkForDuplicates()) {
            $this->redirect($this->createUrl('/site/duplicateCheck', array(
                'moduleName' => 'property',
                'modelName' => 'Property',
                'id' => $id,
                'ref' => 'view',
            )));
        } else {
            $model->duplicateChecked();
            parent::view($model, 'property');
        }
    }

    public function actionShareProperty($id)
    {

        $model = $this->loadModel($id);
        $body = "\n\n\n\n" . Yii::t('property', '{module} Record Details', array(
                '{module}' => Modules::displayName(false)
            )) . " <br />
<br />" . Yii::t('property', 'Name') . ": $model->name
<br />" . Yii::t('app', 'Link') . ": " . CHtml::link($model->name,
                array('/property/property/view', 'id' => $model->id));
        $body = trim($body);

        $errors = array();
        $status = array();
        $email = array();
        if (isset($_POST['email'], $_POST['body'])) {

            $subject = Yii::t('property', "Property Record") . ": $model->name";
            $email['to'] = $this->parseEmailTo($this->decodeQuotes($_POST['email']));
            $body = $_POST['body'];
            // if(empty($email) || !preg_match("/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/",$email))
            if ($email['to'] === false)
                $errors[] = 'email';
            if (empty($body))
                $errors[] = 'body';

            if (empty($errors))
                $status = $this->sendUserEmail($email, $subject, $body);

            if (array_search('200', $status)) {
                $this->redirect(array('view', 'id' => $model->id));
                return;
            }
            if ($email['to'] === false)
                $email = $_POST['email'];
            else
                $email = $this->mailingListToString($email['to']);
        }
        $this->render('shareProperty', array(
            'model' => $model,
            'body' => $body,
            'email' => $email,
            'status' => $status,
            'errors' => $errors
        ));
    }

    public function actionCreate()
    {
        $model = new Property;
        $users = User::getNames();
        unset($users['admin']);
        unset($users['']);
        foreach (Groups::model()->findAll() as $group)
            $users[$group->id] = $group->name;


        if (isset($_POST['Property'])) {

            $model->setX2Fields($_POST['Property']);

            if (isset($_POST['x2ajax'])) {
                $ajaxErrors = $this->quickCreate($model);
            } else {
                if ($model->validate() && $model->checkForDuplicates()) {
                    Yii::app()->user->setState('json_attributes', json_encode($model->attributes));
                    $this->redirect($this->createUrl('/site/duplicateCheck', array(
                        'moduleName' => 'property',
                        'modelName' => 'Property',
                        'id' => null,
                        'ref' => 'create',
                    )));
                } else {
                    if ($model->save()) {
                        $this->redirect(array('view', 'id' => $model->id));
                    }
                }
            }
        }

        if (isset($_POST['x2ajax'])) {
            $this->renderInlineCreateForm($model, isset($ajaxErrors) ? $ajaxErrors : false);
        } else {
            $this->render('create', array(
                'model' => $model,
                'users' => $users,
            ));
        }
    }

    // this nonsense is now done in Property::beforeValidate()
    /*         public function update($model, $oldAttributes,$api){
      // process currency into an INT
      $model->annualRevenue = Formatter::parseCurrency($model->annualRevenue,false);

      if($api==0)
      parent::update($model,$oldAttributes,$api);
      else
      return parent::update($model,$oldAttributes,$api);
      } */

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id)
    {
        $model = $this->loadModel($id);
        if (isset($_POST['Property'])) {
            $model->setX2Fields($_POST['Property']);
            if ($model->save())
                $this->redirect(array('view', 'id' => $model->id));
        }

        $this->render('update', array(
            'model' => $model,
        ));
    }

    /*
      public function actionSaveChanges($id) {
      $property=$this->loadModel($id);
      if(isset($_POST['Property'])) {
      $temp=$property->attributes;
      foreach($property->attributes as $field=>$value){
      if(isset($_POST['Property'][$field])){
      $property->$field=$_POST['Property'][$field];
      }
      }

      // process currency into an INT
      $property->annualRevenue = Formatter::parseCurrency($property->annualRevenue,false);
      $changes=$this->calculateChanges($temp,$property->attributes, $property);
      $property=$this->updateChangelog($property,$changes);
      $property->save();
      $this->redirect(array('view','id'=>$property->id));
      }
      }
     */

    public function actionAddUser($id)
    {
        $users = User::getNames();
        unset($users['admin']);
        unset($users['']);
        foreach (Groups::model()->findAll() as $group) {
            $users[$group->id] = $group->name;
        }
        //$property = Contacts::getAllNames(); // very inefficient with large table
        $model = $this->loadModel($id);
        $users = Property::editUserArray($users, $model);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['Property'])) {

            $temp = $model->assignedTo;
            $tempArr = $model->attributes;
            $model->attributes = $_POST['Property'];
            $arr = $_POST['Property']['assignedTo'];
            $model->assignedTo = Fields::parseUsers($arr);
            if ($temp != "")
                $temp .= ", " . $model->assignedTo;
            else
                $temp = $model->assignedTo;
            $model->assignedTo = $temp;
            // $changes=$this->calculateChanges($tempArr,$model->attributes);
            // $model=$this->updateChangelog($model,$changes);
            if ($model->save())
                $this->redirect(array('view', 'id' => $model->id));
        }

        $this->render('addUser', array(
            'model' => $model,
            'users' => $users,
            //'property' => $property,
            'action' => 'Add'
        ));
    }

    public function actionRemoveUser($id)
    {

        $model = $this->loadModel($id);

        $pieces = explode(', ', $model->assignedTo);
        $pieces = Opportunity::editUsersInverse($pieces);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['Property'])) {
            $temp = $model->attributes;
            $model->attributes = $_POST['Property'];
            $arr = $_POST['Property']['assignedTo'];

            foreach ($arr as $id => $user) {
                unset($pieces[$user]);
            }

            $temp = Fields::parseUsersTwo($pieces);

            $model->assignedTo = $temp;
            // $changes=$this->calculateChanges($temp,$model->attributes);
            // $model=$this->updateChangelog($model,$changes);
            if ($model->save())
                $this->redirect(array('view', 'id' => $model->id));
        }

        $this->render('addUser', array(
            'model' => $model,
            'users' => $pieces,
            'action' => 'Remove'
        ));
    }

    public function delete($id)
    {

        $model = $this->loadModel($id);
        $dataProvider = new CActiveDataProvider('Actions', array(
            'criteria' => array(
                'condition' => 'associationId=' . $id . ' AND associationType=\'property\'',
            )));

        $actions = $dataProvider->getData();
        foreach ($actions as $action) {
            $action->delete();
        }
        $this->cleanUpTags($model);
        $model->delete();
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id)
    {
        if (Yii::app()->request->isPostRequest) {
            $model = $this->loadModel($id);
            Actions::model()->deleteAll('associationId=' . $id . ' AND associationType=\'property\'');
            $this->cleanUpTags($model);
            $model->delete();
        } else
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if (!isset($_GET['ajax']))
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
    }

    // Displays all visible Contact Lists
    public function actionLists()
    {
        $filter = new X2List('search');
        $criteria = new CDbCriteria();
        $criteria->addCondition('modelName = "Property"');
        $criteria->addCondition('type="static" OR type="dynamic"');
        if (!Yii::app()->params->isAdmin) {
            $condition = 'visibility="1" OR assignedTo="Anyone" OR 
                 assignedTo="' . Yii::app()->user->getName() . '"';
            /* x2temp */
            $groupLinks = Yii::app()->db->createCommand()
                ->select('groupId')
                ->from('x2_group_to_user')
                ->where('userId=' . Yii::app()->user->getId())->queryColumn();
            if (!empty($groupLinks))
                $condition .= ' OR assignedTo IN (' . implode(',', $groupLinks) . ')';

            $condition .= 'OR (visibility=2 AND assignedTo IN
                (SELECT username FROM x2_group_to_user WHERE groupId IN
                    (SELECT groupId 
                     FROM x2_group_to_user 
                     WHERE userId=' . Yii::app()->user->getId() . ')
                )
            )';
            $criteria->addCondition($condition);
        }

        $perPage = Profile::getResultsPerPage();

        //$criteria->offset = isset($_GET['page']) ? $_GET['page'] * $perPage - 3 : -3;
        //$criteria->limit = $perPage;
        $criteria->order = 'createDate DESC';
        $filter->compareAttributes($criteria);

        $contactLists = X2Model::model('X2List')->findAll($criteria);

        $totalContacts = X2Model::model('Property')->count();
        $totalMyContacts = X2Model::model('Property')->count('assignedTo="' . Yii::app()->user->getName() . '"');
        $totalNewContacts = X2Model::model('Property')->count('assignedTo="' . Yii::app()->user->getName() . '" AND createDate >= ' . mktime(0, 0, 0));

        $allContacts = new X2List;
        $allContacts->attributes = array(
            'id' => 'all',
            'name' => Yii::t('property', 'All {module}', array('{module}' => Modules::displayName())),
            'description' => '',
            'type' => 'dynamic',
            'visibility' => 1,
            'count' => $totalContacts,
            'createDate' => 0,
            'lastUpdated' => 0,
        );
        $newContacts = new X2List;
        $newContacts->attributes = array(
            'id' => 'new',
            'assignedTo' => Yii::app()->user->getName(),
            'name' => Yii::t('property', 'New {module}', array('{module}' => Modules::displayName())),
            'description' => '',
            'type' => 'dynamic',
            'visibility' => 1,
            'count' => $totalNewContacts,
            'createDate' => 0,
            'lastUpdated' => 0,
        );
        $myContacts = new X2List;
        $myContacts->attributes = array(
            'id' => 'my',
            'assignedTo' => Yii::app()->user->getName(),
            'name' => Yii::t('property', 'My {module}', array('{module}' => Modules::displayName())),
            'description' => '',
            'type' => 'dynamic',
            'visibility' => 1,
            'count' => $totalMyContacts,
            'createDate' => 0,
            'lastUpdated' => 0,
        );
        $contactListData = array(
            $allContacts,
            $myContacts,
            $newContacts,
        );

        $filteredPseudoLists = $filter->filter($contactListData);
        $lists = array_merge($filteredPseudoLists, $contactLists);
        $dataProvider = new CArrayDataProvider($lists, array(
            'pagination' => array('pageSize' => $perPage),
            'sort' => array(
                'attributes' => array(
                    'name' => array(
                        'asc' => 'name asc, id desc',
                        'desc' => 'name desc, id desc',
                    ),
                    // secondary order is needed to fix https://github.com/yiisoft/yii/issues/2082
                    'type' => array(
                        'asc' => 'type asc, id desc',
                        'desc' => 'type desc, id desc',
                    ),
//                    'count' => array (
//                        'asc' => 'count asc, id desc',
//                        'desc' => 'count desc, id desc',
//                    ),
                    'assignedTo' => array(
                        'asc' => 'assignedTo asc, id desc',
                        'desc' => 'assignedTo desc, id desc',
                    ),
                )),
            'totalItemCount' => count($contactLists) + 3,
        ));

        $this->render('listIndex', array(
            'contactLists' => $dataProvider,
            'filter' => $filter,
        ));
    }

    /**
     * Return a JSON encoded list of Contact lists
     */
    public function actionGetLists()
    {
        if (!Yii::app()->user->checkAccess('ContactsAdminAccess')) {
            $condition = ' AND (visibility="1" OR assignedTo="Anyone"  OR assignedTo="' . Yii::app()->user->getName() . '"';
            /* x2temp */
            $groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId=' . Yii::app()->user->getId())->queryColumn();
            if (!empty($groupLinks)) {
                $condition .= ' OR assignedTo IN (' . implode(',', $groupLinks) . ')';
            }

            $condition .= ' OR (visibility=2 AND assignedTo IN
                (SELECT username FROM x2_group_to_user WHERE groupId IN
                (SELECT groupId FROM x2_group_to_user WHERE userId=' . Yii::app()->user->getId() . '))))';
        } else {
            $condition = '';
        }
        // Optional search parameter for autocomplete
        $qterm = isset($_GET['term']) ? $_GET['term'] . '%' : '';
        $static = isset($_GET['static']) && $_GET['static'];
        $weblist = isset($_GET['weblist']) && $_GET['weblist'];
        $result = Yii::app()->db->createCommand()
            ->select('id,name as value')
            ->from('x2_lists')
            ->where(
                ($static ? 'type="static" AND ' : '') .
                ($weblist ? 'type="weblist" AND ' : '') .
                'modelName="Property" AND type!="campaign" 
                    AND name LIKE :qterm' . $condition, array(':qterm' => $qterm))
            ->order('name ASC')
            ->queryAll();
        echo CJSON::encode($result);
    }

    /**
     * Gets a DataProvider for all the property in the specified list,
     * using this Contact model's attributes as a search filter
     */
    public function searchList($id, $pageSize = null)
    {
        $list = X2List::model()->findByPk($id);
        if (isset($list)) {
            $search = $list->queryCriteria();

            $this->compareAttributes($search);
            return new SmartActiveDataProvider('Contacts', array(
                'criteria' => $search,
                'sort' => array(
                    'defaultOrder' => 't.lastUpdated DESC'    // true = ASC
                ),
                'pagination' => array(
                    'pageSize' => isset($pageSize) ? $pageSize : Profile::getResultsPerPage(),
                ),
            ));
        } else {    //if list is not working, return all property
            return $this->searchBase();
        }
    }

    // Shows property in the specified list
    public function actionList($id = null)
    {
        $list = X2List::load($id);

        if (!isset($list)) {
            Yii::app()->user->setFlash(
                'error', Yii::t('app', 'The requested page does not exist.'));
            $this->redirect(array('lists'));
        }

        $model = new Property('search');
        Yii::app()->user->setState('vcr-list', $id);
        $dataProvider = $model->searchList($id);
        $list->count = $dataProvider->totalItemCount;
        $list->runWithoutBehavior('FlowTriggerBehavior', function () use ($list) {
            $list->save();
        });

        X2Flow::trigger('RecordViewTrigger', array('model' => $list));
        $this->render('list', array(
            'listModel' => $list,
            'dataProvider' => $dataProvider,
            'model' => $model,
        ));
    }

    // Lists all contacts assigned to this user
    public function actionMyProperty()
    {
        $model = new Property('search');
        Yii::app()->user->setState('vcr-list', 'myContacts');
        $this->render('index', array('model' => $model));
    }

    // Lists all contacts assigned to this user
    public function actionNewProperty()
    {
        $model = new Property('search');
        Yii::app()->user->setState('vcr-list', 'newContacts');
        $this->render('index', array('model' => $model));
    }


    public function actionUpdateList($id)
    {
        $list = X2List::model()->findByPk($id);

        if (!isset($list))
            throw new CHttpException(400, Yii::t('app', 'This list cannot be found.'));

        if (!$this->checkPermissions($list, 'edit'))
            throw new CHttpException(403, Yii::t('app', 'You do not have permission to modify this list.'));

        $contactModel = new Property;
        $comparisonList = X2List::getComparisonList();
        $fields = $contactModel->getFields(true);

        if ($list->type == 'dynamic') {
            $criteriaModels = X2ListCriterion::model()->findAllByAttributes(array('listId' => $list->id), new CDbCriteria(array('order' => 'id ASC')));

            if (isset($_POST['X2List'], $_POST['X2List']['attribute'], $_POST['X2List']['comparison'], $_POST['X2List']['value'])) {

                $attributes = &$_POST['X2List']['attribute'];
                $comparisons = &$_POST['X2List']['comparison'];
                $values = &$_POST['X2List']['value'];

                if (count($attributes) > 0 && count($attributes) == count($comparisons) && count($comparisons) == count($values)) {

                    $list->attributes = $_POST['X2List'];
                    $list->modelName = 'Property';
                    $list->lastUpdated = time();

                    if (!$list->hasErrors() && $list->save()) {
                        $this->redirect(array('/property/property/list', 'id' => $list->id));
                    }
                }
            }
        } else { //static or campaign lists
            if (isset($_POST['Property'])) {
                $list->attributes = $_POST['Property'];
                $list->modelName = 'Property';
                $list->lastUpdated = time();
                $list->save();
                $this->redirect(array('/property/property/list', 'id' => $list->id));
            }
        }

        if (empty($criteriaModels)) {
            $default = new PropertyCriterion;
            $default->value = '';
            $default->attribute = '';
            $default->comparison = 'contains';
            $criteriaModels[] = $default;
        } else {
            if ($list->type = 'dynamic') {
                foreach ($criteriaModels as $criM) {
                    if (isset($fields[$criM->attribute])) {
                        if ($fields[$criM->attribute]->type == 'link') {
                            $criM->value = implode(',', array_map(function ($c) {
                                    list($name, $id) = Fields::nameAndId($c);
                                    return $name;
                                }, explode(',', $criM->value)
                                )
                            );
                        }
                    }
                }
            }
        }

        $this->render('updateList', array(
            'model' => $list,
            'criteriaModels' => $criteriaModels,
            'users' => User::getNames(),
            // 'attributeList'=>$attributeList,
            'comparisonList' => $comparisonList,
            'listTypes' => array(
                'dynamic' => Yii::t('contacts', 'Dynamic'),
                'static' => Yii::t('contacts', 'Static')
            ),
            'itemModel' => $contactModel,
        ));
    }

    public function actionCreateList($ajax = false)
    {
        $list = new X2List;
        $list->modelName = 'Property';
        $list->type = 'dynamic';
        $list->assignedTo = Yii::app()->user->getName();
        $list->visibility = 1;

        $contactModel = new Property;
        $comparisonList = X2List::getComparisonList();
        if (isset($_POST['X2List'])) {
            $list->type = $_POST['X2List']['type'];
            $list->attributes = $_POST['X2List'];
            $list->modelName = 'Property';
            $list->createDate = time();
            $list->lastUpdated = time();

            if (isset($_POST['X2List'], $_POST['X2List']['attribute'], $_POST['X2List']['comparison'], $_POST['X2List']['value'])) {

                $attributes = &$_POST['X2List']['attribute'];
                $comparisons = &$_POST['X2List']['comparison'];
                $values = &$_POST['X2List']['value'];

                if (count($attributes) > 0 && count($attributes) == count($comparisons) && count($comparisons) == count($values)) {
                    $list->modelName = 'Property';
                    $list->lastUpdated = time();
                }
            }
            if (!$list->hasErrors() && $list->save()) {
                if ($ajax) {
                    echo CJSON::encode($list->attributes);
                    return;
                }
                $this->redirect(array('/property/property/list', 'id' => $list->id));
            }
        }

        if (empty($criteriaModels)) {
            $default = new X2ListCriterion;
            $default->value = '';
            $default->attribute = '';
            $default->comparison = 'contains';
            $criteriaModels[] = $default;
        }

        if ($ajax) {
            $html = $this->renderPartial('createList', array(
                'model' => $list,
                'criteriaModels' => $criteriaModels,
                'users' => User::getNames(),
                // 'attributeList'=>$attributeList,
                'comparisonList' => $comparisonList,
                'listTypes' => array(
                    'dynamic' => Yii::t('property', 'Dynamic'),
                    'static' => Yii::t('property', 'Static')
                ),
                'itemModel' => $contactModel,
            ), false);
            echo $this->processOutput($html);
            return;
        }

        $this->render('createList', array(
            'model' => $list,
            'criteriaModels' => $criteriaModels,
            'users' => User::getNames(),
            // 'attributeList'=>$attributeList,
            'comparisonList' => $comparisonList,
            'listTypes' => array(
                'dynamic' => Yii::t('property', 'Dynamic'),
                'static' => Yii::t('property', 'Static')
            ),
            'itemModel' => $contactModel,
        ));
    }

    public function actionDeleteList()
    {

        $id = isset($_GET['id']) ? $_GET['id'] : 'all';

        if (is_numeric($id))
            $list = X2Model::model('X2List')->findByPk($id);
        if (isset($list)) {

            // check permissions
            if ($this->checkPermissions($list, 'edit'))
                $list->delete();
            else
                throw new CHttpException(403, Yii::t('app', 'You do not have permission to modify this list.'));
        }
        $this->redirect(array('/property/property/lists'));
    }

    /**
     * Lists all models.
     */
    public function actionIndex()
    {

        $model = new Property('search');
        $this->render('index', array('model' => $model));
    }

    public function actionQtip($id)
    {
        $model = $this->loadModel($id);
        $this->renderPartial('qtip', array('model' => $model));
    }

    /**
     * Create a menu for Property
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null)
    {
        $c_Property = Modules::displayName();
        $Account = Modules::displayName(false);
        $modelId = isset($model) ? $model->id : 0;
        $modelName = isset($model) ? $model->name : "";

        /**
         * To show all options:
         * $menuOptions = array(
         *     'all', 'create', 'report', 'import', 'export', 'view', 'edit', 'share',
         *     'delete', 'email', 'attach', 'quotes', 'quick', 'print',
         * );
         */

        $menuItems = array(
            array(
                'name' => 'all',
                'label' => Yii::t('property', 'All {module}', array('{module}' => $c_Property)),
                'url' => array('index'),
            ),
            array(
                'name' => 'create',
                'label' => Yii::t('property', 'Create {module}', array('{module}' => $Account)),
                'url' => array('create')
            ),
            array(
                'name' => 'report',
                'label' => Yii::t('property', '{module} Report', array('{module}' => $c_Property)),
                'url' => array('propertyReport')
            ),
            array(
                'name' => 'import',
                'label' => Yii::t('property', "Import {module}", array('{module}' => $c_Property)),
                'url' => array('admin/importModels', 'model' => 'Property')
            ),
            array(
                'name' => 'export',
                'label' => Yii::t('property', 'Export {module}', array('{module}' => $c_Property)),
                'url' => array('admin/exportModels', 'model' => 'Property')
            ),
            RecordViewLayoutManager::getViewActionMenuListItem($modelId),
            array(
                'name' => 'edit',
                'label' => Yii::t('property', 'Edit {module}', array('{module}' => $Account)),
                'url' => array('update', 'id' => $modelId)
            ),
            array(
                'name' => 'share',
                'label' => Yii::t('property', 'Share {module}', array('{module}' => $Account)),
                'url' => array('shareProperty', 'id' => $modelId)
            ),
            array(
                'name' => 'delete',
                'label' => Yii::t('property', 'Delete {module}', array('{module}' => $Account)),
                'url' => '#',
                'linkOptions' => array(
                    'submit' => array('delete', 'id' => $modelId),
                    'confirm' => 'Are you sure you want to delete this item?'
                )
            ),
            array(
                'name' => 'lists',
                'label' => Yii::t('property', 'Lists'),
                'url' => array('lists')
            ),
            array(
                'name' => 'email',
                'label' => Yii::t('app', 'Send Email'),
                'url' => '#',
                'linkOptions' => array('onclick' => 'toggleEmailForm(); return false;')
            ),
            ModelFileUploader::menuLink(),
            array(
                'name' => 'quotes',
                'label' => Yii::t('quotes', 'Quotes/Invoices'),
                'url' => 'javascript:void(0)',
                'linkOptions' => array(
                    'onclick' => 'x2.inlineQuotes.toggle(); return false;')
            ),
            array(
                'name' => 'createList',
                'label' => Yii::t('contacts', 'Create List'),
                'url' => array('createList')
            ),
            array(
                'name' => 'viewList',
                'label' => Yii::t('contacts', 'View List'),
                'url' => array('list', 'id' => $modelId)
            ),
            array(
                'name' => 'editList',
                'label' => Yii::t('contacts', 'Edit List'),
                'url' => array('updateList', 'id' => $modelId)
            ),
            array(
                'name' => 'deleteList',
                'label' => Yii::t('contacts', 'Delete List'),
                'url' => '#',
                'linkOptions' => array(
                    'submit' => array('deleteList', 'id' => $modelId),
                    'confirm' => 'Are you sure you want to delete this item?')
            ),
            array(
                'name' => 'quick',
                'label' => Yii::t('app', 'Quick Create'),
                'url' => array('/site/createRecords', 'ret' => 'property'),
                'linkOptions' => array(
                    'id' => 'x2-create-multiple-records-button',
                    'class' => 'x2-hint',
                    'title' => Yii::t('app', 'Create a {contact}, {property}, and {opportunity}.', array(
                        '{property}' => $Account,
                        '{contact}' => Modules::displayName(false, "Contacts"),
                        '{opportunity}' => Modules::displayName(false, "Opportunities"),
                    )))
            ),
            array(
                'name' => 'print',
                'label' => Yii::t('app', 'Print Record'),
                'url' => '#',
                'linkOptions' => array(
                    'onClick' => "window.open('" .
                        Yii::app()->createUrl('/site/printRecord', array(
                            'modelClass' => 'Property',
                            'id' => $modelId,
                            'pageTitle' => Yii::t('app', 'Property') . ': ' . $modelName
                        )) . "');"
                ),
            ),
            RecordViewLayoutManager::getEditLayoutActionMenuListItem(),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }
}
