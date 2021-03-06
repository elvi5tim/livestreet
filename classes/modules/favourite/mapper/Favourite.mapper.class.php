<?php
/*-------------------------------------------------------
*
*   LiveStreet Engine Social Networking
*   Copyright © 2008 Mzhelskiy Maxim
*
*--------------------------------------------------------
*
*   Official site: www.livestreet.ru
*   Contact e-mail: rus.engine@gmail.com
*
*   GNU General Public License, version 2:
*   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*
---------------------------------------------------------
*/

class ModuleFavourite_MapperFavourite extends Mapper {	
		
	public function AddFavourite(ModuleFavourite_EntityFavourite $oFavourite) {
		$sql = "
			INSERT INTO ".Config::Get('db.table.favourite')." 
				( target_id, target_type, user_id, tags )
			VALUES
				(?d, ?, ?d, ?)
		";			
		if ($this->oDb->query(
			$sql,
			$oFavourite->getTargetId(),
			$oFavourite->getTargetType(),
			$oFavourite->getUserId(),
			$oFavourite->getTags()
		)===0) {
			return true;
		}		
		return false;
	}

	public function UpdateFavourite(ModuleFavourite_EntityFavourite $oFavourite) {
		$sql = "
			UPDATE ".Config::Get('db.table.favourite')."
				SET tags = ? WHERE user_id = ?d and target_id = ?d and target_type = ?
		";
		if ($this->oDb->query(
			$sql,
			$oFavourite->getTags(),
			$oFavourite->getUserId(),
			$oFavourite->getTargetId(),
			$oFavourite->getTargetType()
		)!==false) {
			return true;
		}
		return false;
	}
	
	public function GetFavouritesByArray($aArrayId,$sTargetType,$sUserId) {
		if (!is_array($aArrayId) or count($aArrayId)==0) {
			return array();
		}				
		$sql = "SELECT *							 
				FROM ".Config::Get('db.table.favourite')."
				WHERE 			
					user_id = ?d
					AND		
					target_id IN(?a) 	
					AND
					target_type = ? ";
		$aFavourites=array();
		if ($aRows=$this->oDb->select($sql,$sUserId,$aArrayId,$sTargetType)) {
			foreach ($aRows as $aRow) {
				$aFavourites[]=Engine::GetEntity('Favourite',$aRow);
			}
		}		
		return $aFavourites;
	}	
	
	public function DeleteFavourite(ModuleFavourite_EntityFavourite $oFavourite) {
		$sql = "
			DELETE FROM ".Config::Get('db.table.favourite')." 
			WHERE
				user_id = ?d
			AND
				target_id = ?d
			AND 
				target_type = ?				
		";			
		if ($this->oDb->query(
			$sql,
			$oFavourite->getUserId(),
			$oFavourite->getTargetId(),
			$oFavourite->getTargetType()
		)) {
			return true;
		}		
		return false;
	}

	public function DeleteTags($oFavourite) {
		$sql = "
			DELETE FROM ".Config::Get('db.table.favourite_tag')."
			WHERE
				user_id = ?d
				AND
				target_type = ?
				AND
				target_id = ?d
		";
		if ($this->oDb->query(
			$sql,
			$oFavourite->getUserId(),
			$oFavourite->getTargetType(),
			$oFavourite->getTargetId()
		)) {
			return true;
		}
		return false;
	}

	public function AddTag($oTag) {
		$sql = "
			INSERT INTO ".Config::Get('db.table.favourite_tag')."
				SET target_id = ?d, target_type = ?, user_id = ?d, is_user = ?d, text =?
		";
		if ($this->oDb->query(
			$sql,
			$oTag->getTargetId(),
			$oTag->getTargetType(),
			$oTag->getUserId(),
			$oTag->getIsUser(),
			$oTag->getText()
		)===0) {
			return true;
		}
		return false;
	}
	
	public function SetFavouriteTargetPublish($aTargetId,$sTargetType,$iPublish) {
		$sql = "
			UPDATE ".Config::Get('db.table.favourite')." 
			SET 
				target_publish = ?d
			WHERE				
				target_id IN(?a)
			AND
				target_type = ?				
		";			
		return $this->oDb->query($sql,$iPublish,$aTargetId,$sTargetType); 		
	}	
	
	public function GetFavouritesByUserId($sUserId,$sTargetType,&$iCount,$iCurrPage,$iPerPage,$aExcludeTarget=array()) {	
		$sql = "			
			SELECT target_id										
			FROM ".Config::Get('db.table.favourite')."								
			WHERE 
					user_id = ?
				AND
					target_publish = 1
				AND
					target_type = ? 
				{ AND target_id NOT IN (?a) }		
            ORDER BY target_id DESC	
            LIMIT ?d, ?d ";
		
		$aFavourites=array();		
		if ($aRows=$this->oDb->selectPage(
				$iCount,
				$sql,
				$sUserId,
				$sTargetType,
				(count($aExcludeTarget) ? $aExcludeTarget : DBSIMPLE_SKIP),
				($iCurrPage-1)*$iPerPage, 
				$iPerPage
		)) {
			foreach ($aRows as $aFavourite) {
				$aFavourites[]=$aFavourite['target_id'];
			}			
		}		
		return $aFavourites;
	}
		
	public function GetCountFavouritesByUserId($sUserId,$sTargetType,$aExcludeTarget) {
		$sql = "SELECT 		
					count(target_id) as count									
				FROM 
					".Config::Get('db.table.favourite')."								
				WHERE 
						user_id = ?
					AND
						target_publish = 1
					AND
						target_type = ?
					{ AND target_id NOT IN (?a) }		
					;";
		return ( $aRow=$this->oDb->selectRow(
						$sql,$sUserId,
						$sTargetType,
						(count($aExcludeTarget) ? $aExcludeTarget : DBSIMPLE_SKIP)
					) 
				)
					? $aRow['count']
					: false;
	}	
	
	public function GetFavouriteOpenCommentsByUserId($sUserId,&$iCount,$iCurrPage,$iPerPage) {	
		$sql = "
			SELECT f.target_id										
			FROM 
				".Config::Get('db.table.favourite')." AS f,
				".Config::Get('db.table.comment')." AS c,
				".Config::Get('db.table.topic')." AS t,
				".Config::Get('db.table.blog')." AS b	
			WHERE 
					f.user_id = ?d
				AND
					f.target_publish = 1
				AND
					f.target_type = 'comment'
				AND
					f.target_id = c.comment_id
				AND 
					c.target_id = t.topic_id
				AND 
					t.blog_id = b.blog_id
				AND 
					b.blog_type IN ('open', 'personal')	
            ORDER BY target_id DESC	
            LIMIT ?d, ?d ";
		
		$aFavourites=array();		
		if ($aRows=$this->oDb->selectPage(
				$iCount, $sql, $sUserId,
				($iCurrPage-1)*$iPerPage, $iPerPage
		)) {
			foreach ($aRows as $aFavourite) {
				$aFavourites[]=$aFavourite['target_id'];
			}			
		}		
		return $aFavourites;
	}	

	public function GetCountFavouriteOpenCommentsByUserId($sUserId) {
		$sql = "SELECT 		
					count(f.target_id) as count									
				FROM 
					".Config::Get('db.table.favourite')." AS f,
					".Config::Get('db.table.comment')." AS c,
					".Config::Get('db.table.topic')." AS t,
					".Config::Get('db.table.blog')." AS b	
				WHERE 
						f.user_id = ?d
					AND
						f.target_publish = 1
					AND
						f.target_type = 'comment'
					AND
						f.target_id = c.comment_id
					AND 
						c.target_id = t.topic_id
					AND 
						t.blog_id = b.blog_id
					AND 
						b.blog_type IN ('open', 'personal')		
					;";				
		return ( $aRow=$this->oDb->selectRow($sql,$sUserId) )
					? $aRow['count']
					: false;
	}

	public function GetFavouriteOpenTopicsByUserId($sUserId,&$iCount,$iCurrPage,$iPerPage) {	
		$sql = "
			SELECT f.target_id										
			FROM 
				".Config::Get('db.table.favourite')." AS f,
				".Config::Get('db.table.topic')." AS t,
				".Config::Get('db.table.blog')." AS b	
			WHERE 
					f.user_id = ?d
				AND
					f.target_publish = 1
				AND
					f.target_type = 'topic'
				AND
					f.target_id = t.topic_id
				AND 
					t.blog_id = b.blog_id
				AND 
					b.blog_type IN ('open', 'personal')	
            ORDER BY target_id DESC	
            LIMIT ?d, ?d ";
		
		$aFavourites=array();		
		if ($aRows=$this->oDb->selectPage(
				$iCount, $sql, $sUserId,
				($iCurrPage-1)*$iPerPage, $iPerPage
		)) {
			foreach ($aRows as $aFavourite) {
				$aFavourites[]=$aFavourite['target_id'];
			}			
		}		
		return $aFavourites;
	}	

	public function GetCountFavouriteOpenTopicsByUserId($sUserId) {
		$sql = "SELECT 		
					count(f.target_id) as count									
				FROM 
					".Config::Get('db.table.favourite')." AS f,
					".Config::Get('db.table.topic')." AS t,
					".Config::Get('db.table.blog')." AS b	
				WHERE 
						f.user_id = ?d
					AND
						f.target_publish = 1
					AND
						f.target_type = 'topic'
					AND
						f.target_id = t.topic_id
					AND 
						t.blog_id = b.blog_id
					AND 
						b.blog_type IN ('open', 'personal')		
					;";				
		return ( $aRow=$this->oDb->selectRow($sql,$sUserId) )
					? $aRow['count']
					: false;
	}	
	
	public function DeleteFavouriteByTargetId($aTargetId,$sTargetType) {
		$sql = "
			DELETE FROM ".Config::Get('db.table.favourite')." 
			WHERE 
				target_id IN(?a) 
				AND 
				target_type = ? ";			
		if ($this->oDb->query($sql,$aTargetId,$sTargetType)) {
			return true;
		}
		return false;
	}

	public function DeleteTagByTarget($aTargetId,$sTargetType) {
		$sql = "
			DELETE FROM ".Config::Get('db.table.favourite_tag')."
			WHERE
				target_type = ?
				AND
				target_id IN(?a)
				";
		if ($this->oDb->query($sql,$sTargetType,$aTargetId)) {
			return true;
		}
		return false;
	}

	public function GetGroupTags($iUserId,$sTargetType,$bIsUser,$iLimit) {
		$sql = "SELECT
			text,
			count(text)	as count
			FROM
				".Config::Get('db.table.favourite_tag')."
			WHERE
				1=1
				{AND user_id = ?d }
				{AND target_type = ? }
				{AND is_user = ?d }
			GROUP BY
				text
			ORDER BY
				count desc
			LIMIT 0, ?d
				";
		$aReturn=array();
		$aReturnSort=array();
		if ($aRows=$this->oDb->select($sql,$iUserId,$sTargetType,is_null($bIsUser) ? DBSIMPLE_SKIP : $bIsUser,$iLimit)) {
			foreach ($aRows as $aRow) {
				$aReturn[mb_strtolower($aRow['text'],'UTF-8')]=$aRow;
			}
			ksort($aReturn);
			foreach ($aReturn as $aRow) {
				$aReturnSort[]=Engine::GetEntity('ModuleFavourite_EntityTag',$aRow);
			}
		}
		return $aReturnSort;
	}

	public function GetTags($aFilter,$aOrder,&$iCount,$iCurrPage,$iPerPage) {
		$aOrderAllow=array('target_id','user_id','is_user');
		$sOrder='';
		foreach ($aOrder as $key=>$value) {
			if (!in_array($key,$aOrderAllow)) {
				unset($aOrder[$key]);
			} elseif (in_array($value,array('asc','desc'))) {
				$sOrder.=" {$key} {$value},";
			}
		}
		$sOrder=trim($sOrder,',');
		if ($sOrder=='') {
			$sOrder=' target_id desc ';
		}

		$sql = "SELECT
					*
				FROM
					".Config::Get('db.table.favourite_tag')."
				WHERE
					1 = 1
					{ AND user_id = ?d }
					{ AND target_type = ? }
					{ AND target_id = ?d }
					{ AND is_user = ?d }
					{ AND text = ? }
				ORDER by {$sOrder}
				LIMIT ?d, ?d ;
					";
		$aResult=array();
		if ($aRows=$this->oDb->selectPage($iCount,$sql,
										  isset($aFilter['user_id']) ? $aFilter['user_id'] : DBSIMPLE_SKIP,
										  isset($aFilter['target_type']) ? $aFilter['target_type'] : DBSIMPLE_SKIP,
										  isset($aFilter['target_id']) ? $aFilter['target_id'] : DBSIMPLE_SKIP,
										  isset($aFilter['is_user']) ? $aFilter['is_user'] : DBSIMPLE_SKIP,
										  isset($aFilter['text']) ? $aFilter['text'] : DBSIMPLE_SKIP,
										  ($iCurrPage-1)*$iPerPage, $iPerPage
		)) {
			foreach ($aRows as $aRow) {
				$aResult[]=Engine::GetEntity('ModuleFavourite_EntityTag',$aRow);
			}
		}
		return $aResult;
	}
}
?>