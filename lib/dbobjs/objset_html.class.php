<?
class ObjSetHtml {

	public static function makeListHeaderForm($list, $url) {
		$out = '';

		if ($list->hasPrev()) {
			$out .= '<form class="inl" method="post" action="'.$url.'" >
				'.Form::inputHtml("hidden", "page", $list->prevI()).'
				<input type="submit" value="'.t('Previous').'" /></form>';
		}

		if ($list->hasNext()) {
			$out .= '<form class="inl" method="post" action="'.$url.'" >
				'.Form::inputHtml("hidden", "page", $list->nextI()).'
				<input type="submit" value="'.t('Next').'" /></form>';
		}

		return $out;
	}

	public static function makeListHeader($list, $url) {
		$links = self::getListLinks($list);
		$out = array();
		if($links[0] !== '') {
			$out[] = '<a href="'.$url.'&'.$links[0].'"><img src="media/img/left8.png" '.HtmlBlock::altTitle(t('Previous')).' />'.t('Previous').'</a>';
		}
		if($links[1] !== '') {
			$out[] = '<a href="'.$url.'&'.$links[1].'">'.t('Next').'<img src="media/img/right8.png" '.HtmlBlock::altTitle(t('Next')).' /></a>';
		}
		return "<p>".join('&nbsp;',$out).'</p>';
	}

	private static function getListLinks($list) {
		$prev_url = '';
		if ($list->hasPrev()) {
			$prev_url .= 'page='.$list->prevI();
		}
		$next_url = '';
		if ($list->hasNext()) {
			$next_url .= 'page='.$list->nextI();
		}
		return array($prev_url, $next_url);
	}

}

