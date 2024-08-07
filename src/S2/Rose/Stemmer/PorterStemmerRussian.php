<?php

namespace S2\Rose\Stemmer;

/**
 * @see http://forum.dklab.ru/php/advises/HeuristicWithoutTheDictionaryExtractionOfARootFromRussianWord.html
 */
class PorterStemmerRussian extends AbstractStemmer implements StemmerInterface
{
    const SUPPORTS_REGEX = '#^[а-яА-ЯёЁ\-0-9]*$#Su';

    const VOWEL            = '/аеиоуыэюя/Su';
    const PERFECTIVEGROUND = '/((ив|ивши|ившись|ыв|ывши|ывшись)|((?<=[ая])(в|вши|вшись)))$/Su';
    const REFLEXIVE        = '/(с[яь])$/Su';
    const ADJECTIVE        = '/(ее|ие|ые|ое|ими|ыми|ей|ий|ый|ой|ем|им|ым|ом|его|ого|ему|ому|их|ых|еых|ую|юю|ая|яя|ою|ею)$/Su';
    const PARTICIPLE       = '/((ивш|ывш|ующ)|((?<=[ая])(ем|нн|вш|ющ|щ)))$/Su';
    const VERB             = '/((ила|ыла|ена|ейте|уйте|ите|или|ыли|ей|уй|ил|ыл|им|ым|ен|ило|ыло|ено|ят|ует|уют|ит|ыт|ены|ить|ыть|ишь|ую|ю)|((?<=[ая])(ла|на|ете|йте|ли|й|л|ем|н|ло|но|ет|ют|ны|ть|ешь|нно)))$/Su';
    const NOUN             = '/(а|ев|ов|ие|ье|е|иями|ями|ами|еи|ии|и|ией|ей|ой|ий|й|иям|ям|ием|ем|ам|ом|о|у|ах|иях|ях|ы|ь|у|ию|ью|ю|ия|ья|я)$/Su';
    const RVRE             = '/^(.*?[аеиоуыэюя])(.*)$/Su';
    const DERIVATIONAL     = '/[^аеиоуыэюя][аеиоуыэюя]+[^аеиоуыэюя]+[аеиоуыэюя].*(?<=о)сть?$/Su';

    protected static $irregularWords = [
        'и'       => '',
        'или'     => '',
        'когда'   => '',
        'где'     => '',
        'куда'    => '',
        'если'    => '',
        'тире'    => '',
        'после'   => '',
        'перед'   => '',
        'менее'   => '',
        'более'   => '',
        'меньше'  => '',
        'больше'  => '',
        'уж'      => '',
        'уже'     => '',
        'там'     => '',
        'тут'     => '',
        'туда'    => '',
        'сюда'    => '',
        'оттуда'  => '',
        'отсюда'  => '',
        'здесь'   => '',
        'впрочем' => '',
        'зачем'   => '',
        'затем'   => '',
        'зато'    => '',
        'пусть'   => '',
        'никогда' => '',
        'иногда'  => '',
        'тогда'   => '',
        'всегда'  => '',
        'сейчас'  => '',
        'теперь'  => '',
        'сегодня' => '',
        'конечно' => '',
        'вместе'  => '',
        'вместо'  => '',
        'однако'  => '',
        'вообще'  => '',
        'вполне'  => '',
        'очень'   => '',
        'опять'   => '',
        'хоть'    => '',
        'хотя'    => '',
        'почти'   => '',
        'тоже'    => '',
        'также'   => '',
        'даже'    => '',
        'как'     => '',
        'так'     => '',
        'вот'     => '',
        'нет'     => '',
        'нету'    => 'нет',
        'вдруг'   => '',
        'через'   => '',
        'между'   => '',
        'еще'     => '',
        'ещё'     => 'еще',
        'чуть'    => '',
        'разве'   => '',
        'ведь'    => '',
        'нибудь'  => '',
        'будто'   => '',
        'можно'   => '',
        'нельзя'  => '',
        'хорошо'  => '',
        'только'  => '',
        'просто'  => '',
        'почему'  => '',
        'потому'  => '',
        'всего'   => '',
        'чтоб'    => '',
        'чтобы'   => 'чтоб',
        'лишь'    => '',
        'вон'     => '',

        'под'   => '',
        'подо'  => 'под',
        'об'    => '',
        'от'    => '',
        'без'   => '',
        'безо'  => 'без',
        'над'   => '',
        'надо'  => '',
        'из'    => '',
        'из-за' => '',

        'что'  => '',
        'чего' => 'что',
        'чему' => 'что',
        'чем'  => 'что',
        'чём'  => 'что',

        'кто'  => '',
        'кого' => 'кто',
        'кому' => 'кто',
        'кем'  => 'кто',
        'ком'  => 'кто',

        'ничто'  => '',
        'ничего' => 'ничто',
        'ничему' => 'ничто',
        'ничем'  => 'ничто',
        'ничём'  => 'ничто',

        'никто'  => '',
        'никого' => 'никто',
        'никому' => 'никто',
        'никем'  => 'никто',
        'ником'  => 'никто',

        'все-таки'     => '',
        'во-первых'    => '',
        'во-вторых'    => '',
        'в-третьих'    => '',

        // Some popular prefixes and postfixes. TODO come up with a more systematic approach
        'как-то'       => '',
        'как-нибудь'   => '',
        'где-то'       => '',
        'когда-то'     => '',
        'когда-нибудь' => '',
        'куда-то'      => '',
        'почему-то'    => '',
        'вообще-то'    => '',

        'что-то'  => 'что-то',
        'чего-то' => 'что-то',
        'чему-то' => 'что-то',
        'чем-то'  => 'что-то',
        'чём-то'  => 'что-то',

        'что-нибудь'  => 'что-нибудь',
        'чего-нибудь' => 'что-нибудь',
        'чему-нибудь' => 'что-нибудь',
        'чем-нибудь'  => 'что-нибудь',
        'чём-нибудь'  => 'что-нибудь',

        'кое-что'  => 'кое-что',
        'кое-чего' => 'кое-что',
        'кое-чему' => 'кое-что',
        'кое-чем'  => 'кое-что',
        'кое-чём'  => 'кое-что',

        'кто-то'  => 'кто-то',
        'кого-то' => 'кто-то',
        'кому-то' => 'кто-то',
        'кем-то'  => 'кто-то',
        'ком-то'  => 'кто-то',

        'кто-нибудь'  => 'кто-нибудь',
        'кого-нибудь' => 'кто-нибудь',
        'кому-нибудь' => 'кто-нибудь',
        'кем-нибудь'  => 'кто-нибудь',
        'ком-нибудь'  => 'кто-нибудь',

        'кое-кто'  => 'кое-кто',
        'кое-кого' => 'кое-кто',
        'кое-кому' => 'кое-кто',
        'кое-кем'  => 'кое-кто',
        'кое-ком'  => 'кое-кто',

        'печать' => 'печат', // стеммер считает, что это глагол

        'шея'   => '',
        'шеи'   => 'шея',
        'шее'   => 'шея',
        'шею'   => 'шея',
        'шеей'  => 'шея',
        'шей'   => 'шея',
        'шеям'  => 'шея',
        'шеями' => 'шея',
        'шеях'  => 'шея',

        'идея'   => 'идея',
        'идеи'   => 'идея',
        'идее'   => 'идея',
        'идею'   => 'идея',
        'идеей'  => 'идея',
        'идей'   => 'идея',
        'идеям'  => 'идея',
        'идеями' => 'идея',
        'идеях'  => 'идея',

        'имя'     => '',
        'имени'   => 'имя',
        'именем'  => 'имя',
        'имена'   => 'имя',
        'имен'    => 'имя',
        'именам'  => 'имя',
        'именами' => 'имя',
        'именах'  => 'имя',

        'время'     => '',
        'времени'   => 'время',
        'временем'  => 'время',
        'времена'   => 'время',
        'времен'    => 'время',
        'временам'  => 'время',
        'временами' => 'время',
        'временах'  => 'время',

        'экзамен'  => '',
        'экзамена' => 'экзамен',
        'экзамены' => 'экзамен',
        'массив'   => '',
        'метро'    => '',
        'кино'     => '',
        'фото'     => '',

        'один'   => '',
        'одного' => 'один',
        'одному' => 'один',
        'одним'  => 'один',
        'одном'  => 'один',

        'одна'  => '',
        'одной' => 'одна',
        'одну'  => 'одна',

        'татьяна' => 'татьян',
        'татьяны' => 'татьян',

        'он'   => '',
        'его'  => 'он',
        'него' => 'он',
        'ему'  => 'он',
        'нему' => 'он',
        'ним'  => 'он', // конфликт с "они"
//		'им'   => 'он', // конфликт с "они"
        'нем'  => 'он', // конфликт с "нем" - немой
        'нём'  => 'он',

        'она' => '',
        'её'  => 'она',
        'ее'  => 'она',
        'ей'  => 'она',
        'ней' => 'она',

        'я'    => '',
        'меня' => 'я',
        'мне'  => 'я',
        'мной' => 'я',

        'ты'    => '',
        'тебя'  => 'ты',
        'тебе'  => 'ты',
        'тобой' => 'ты',

        'вас'  => 'вы',
        'вам'  => 'вы',
        'вами' => 'вы',

        'нас'  => 'мы',
        'нам'  => 'мы',
        'нами' => 'мы',

        'они'  => '',
        'их'   => 'они',
        'им'   => 'они',
        'ими'  => 'они',
        'ними' => 'они',
        'них'  => 'они',

        'ересь'  => 'ерес',
        'ереси'  => 'ерес',
        'ересью' => 'ерес',

        'домен'  => '',
        'домена' => 'домен',
        'домены' => 'домен',

        'модем'  => '',
        'модема' => 'модем',
        'модему' => 'модем',

        'ищу'   => 'иска',
        'ищешь' => 'иска',
        'ищет'  => 'иска',
        'ищем'  => 'иска',
        'ищете' => 'иска',
        'ищут'  => 'иска',
        'ищи'   => 'иска',
        'ищите' => 'иска',

        'мочь'   => '',
        'могу'   => 'мочь',
        'можешь' => 'мочь',
        'может'  => 'мочь',
        'можем'  => 'мочь',
        'можете' => 'мочь',
        'могут'  => 'мочь',
        'мог'    => 'мочь',
        'могла'  => 'мочь',
        'могло'  => 'мочь',
        'могли'  => 'мочь',

        'другой'  => 'друго',
        'другого' => 'друго',
        'другому' => 'друго',
        'другим'  => 'друго',
        'другом'  => 'друго', // конфликт с "друг"
    ];

    protected $cache = [];

    /**
     * {@inheritdoc}
     */
    public function stemWord(string $word, bool $normalize = true): string
    {
        if ($normalize) {
            $word = \mb_strtolower($word);
            $word = \str_replace('ё', 'е', $word);
        }

        if (isset($this->cache[$word])) {
            return $this->cache[$word];
        }

        /**
         * TODO How to deal with postfixes like "кто-либо" -> "кого-либо"?
         * Ignoring postfix is not an option - there are a lot of trash results found.
         * Transforming like `stem('кто') . '-либо'` requires some hack for reverse transform when highlighting.
         *
         * @see \S2\Rose\Stemmer\IrregularWordsStemmerInterface::irregularWordsFromStems
         */
        // $word = preg_replace('/^(.*)-(то|либо|нибудь)$/Su', '-\\2-\\1', $word);

        if (isset(self::$irregularWords[$word])) {
            return $this->cache[$word] = (self::$irregularWords[$word] !== '' ? self::$irregularWords[$word] : $word);
        }

        if (!\preg_match(self::SUPPORTS_REGEX, $word)) {
            return $this->nextStemmer !== null ? $this->nextStemmer->stemWord($word) : $word;
        }

        $stem = $word;
        do {
            if (!\preg_match(self::RVRE, $word, $p)) {
                break;
            }

            $start = $p[1];
            $RV    = $p[2];
            if (!$RV) {
                break;
            }

            # Step 1
            if (!self::s($RV, self::PERFECTIVEGROUND, '')) {
                self::s($RV, self::REFLEXIVE, '');

                if (self::s($RV, self::ADJECTIVE, '')) {
                    self::s($RV, self::PARTICIPLE, '');
                } else {
                    if (!self::s($RV, self::VERB, '')) {
                        self::s($RV, self::NOUN, '');
                    }
                }
            }

            # Step 2
            self::s($RV, '/и$/Su', '');

            # Step 3
            if (\preg_match(self::DERIVATIONAL, $RV)) {
                self::s($RV, '/ость?$/Su', '');
            }

            # Step 4
            if (!self::s($RV, '/ь$/Su', '')) {
                self::s($RV, '/ейше?/Su', '');
                self::s($RV, '/нн$/Su', 'н');
            }

            $stem = $start . $RV;
        } while (false);

        $this->cache[$word] = $stem;

        return $stem;
    }

    protected static function s(&$s, $re, $to)
    {
        $orig = $s;
        $s    = \preg_replace($re, $to, $s);

        return $orig !== $s;
    }

    /**
     * {@inheritdoc}
     */
    protected function getIrregularWords(): array
    {
        return self::$irregularWords;
    }

    /**
     * {@inheritdoc}
     */
    public function getRegexTransformationRules(): array
    {
        return array_merge([
            '#е#i' => '[её]',
        ], $this->nextStemmer !== null ? $this->nextStemmer->getRegexTransformationRules() : []);
    }
}
