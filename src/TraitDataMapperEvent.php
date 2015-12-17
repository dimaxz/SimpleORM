<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace SimpleORM;

/**
 * Description of TraitDataMapperEvent
 *
 * @author d.lanec
 */
trait TraitDataMapperEvent {

	/**
	 * Перед сохранением извелкаем объект и дополняем массив для записи, недостающими полями
	 * @param \Autoprice\Domain\Price\EntityInterface $Entity
	 * @param type $data
	 */
	protected function onPrepareData(\SimpleORM\EntityInterface $Entity, &$data) {
		foreach ($this->mapping_fields as $field => $cfg) {

			if ($cfg['null'] === false && empty($data[$cfg['field']])) {
				$data[$cfg['field']] = $cfg['default'];
			}
		}
	}

	/**
	 * На успешное удаление
	 * @param \SimpleORM\EntityInterface $Entity
	 */
	protected function onBeforeDelete(EntityInterface $Entity) {
		foreach ($this->relations as $alias => $cfg) {
			$mapper = $cfg['mapper'];
			//если связь один к одному то удаляем сущность
			if ($cgg['reltype'] == 'has_one') {
				$Entity = $Entity->{'get' . $alias}();
				if (!$mapper->delete($Entity)) {
					throw new \Autoprice\Exceptions\EntityNotDeleteException('Unable to delete Entity!');
				}
			}
		}

		return true;
	}

	/**
	 * Событие перед сохранением
	 * @param \SimpleORM\EntityInterface $Entity
	 */
	protected function onAfterSave(EntityInterface $Entity) {

		$this->getAdapter()->startTransaction();

		$rel_list = $this->createListRelation();

		foreach ($rel_list as $obj_path => $mapper) {

			$get_path = str_replace('#', '->get', '$o = $Entity' . $obj_path . ';');
			$set_path = str_replace(['#', '();'], ['->set', '($o);'], '$Entity' . $obj_path . ';');
			
			eval($get_path); //получаем объект таким образом дабы не гулять по корневому объекту
			
			if (is_object($o) && is_a($o,'SimpleORM\EntityInterface') && $this->DI->get($mapper)->saveWithoutEvents($o)) {
				eval($set_path);
			}
			unset($o);
		}
	}

	/**
	 * После успешного сохранения
	 * @param \SimpleORM\EntityInterface $Entity
	 */
	protected function onBeforeSave(EntityInterface $Entity) {

		$this->getAdapter()->endTransaction();

//		foreach ($this->relations as $alias => $mapper) {
//		
//			$SaveEntity = $Entity->{'get'.$alias}();
//			
//			if(!$mapper->save($SaveEntity)){
//				throw new \Autoprice\Exceptions\EntityNotSaveException('Unable to save Entity!');
//			}
//			
//			unset($SaveEntity);
//		}
//		
//		return true;
	}

}
