<?php

namespace TAO\View\Twig;

class SectionParser extends \Twig_TokenParser
{

    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();
        $token = $this->parser->getStream()->expect(\Twig_Token::NAME_TYPE);
        $name = $token->getValue();

        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(array($this, 'decideTagEnd'), true);
        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

        return new SectionNode($name, $body, $lineno);
    }

    public function decideTagEnd(\Twig_Token $token)
    {
        return $token->test('endsection');
    }

    /**
     * @return bool
     */
    public function getTag()
    {
        return 'section';
    }
}