<?php

namespace Rouffj\Tests\Twig;

use Rouffj\Tests\TestCase;
use Twig_Token;
use Twig_Lexer;

class InternalTest extends TestCase
{
    private  $twig = null;

    private $templates = array(
        'tpl3.html.twig' => '{% set var1 = "bar" %}{% if (1 == 1) %} {% set var2 = "test" %} {% endif %}{% block nb1 %}block body 1{% endblock%}{% macro macro1() %}test{% endmacro%}{% include "tpl4.html.twig" %}',
        'tpl4.html.twig' => '{% set var3 = "barss" %}',
    );

    public function doSetUp()
    {
        $this->twig = $this->container->get('twig');
        $this->twig->setCache(false);
        $this->twig->setLoader(new \Twig_Loader_String());
    }

    public function testHowToIgnoreUnregisteredTag()
    {
        $this->twig->addTokenParser(new MyTagTokenParser());
        $tokenStream = $this->twig->tokenize("{{'Hello'}} {% mytag %} {% endmytag %}");
        $this->twig->parse($tokenStream);
    }

    public function testHowIsWorkingTwigLexer()
    {
        $lexer = new Twig_Lexer($this->twig);

        $tokenStream = $lexer->tokenize("{{'Hello'}}");
        $this->assertEquals(new Twig_Token(Twig_Token::VAR_START_TYPE, '', 1), $tokenStream->next());
        $this->assertEquals(new Twig_Token(Twig_Token::STRING_TYPE, 'Hello', 1), $tokenStream->next());
        $this->assertEquals(new Twig_Token(Twig_Token::VAR_END_TYPE, '', 1), $tokenStream->next());
        $this->assertEquals(new Twig_Token(Twig_Token::EOF_TYPE, '', 1), $tokenStream->getCurrent());

        $tokenStream = $lexer->tokenize("<html> {{  'world'}}</html>");
        $this->assertEquals(new Twig_Token(Twig_Token::TEXT_TYPE, '<html> ', 1), $tokenStream->next());
        $this->assertEquals(new Twig_Token(Twig_Token::VAR_START_TYPE, '', 1), $tokenStream->next());
        $this->assertEquals(new Twig_Token(Twig_Token::STRING_TYPE, 'world', 1), $tokenStream->next());
        $this->assertEquals(new Twig_Token(Twig_Token::VAR_END_TYPE, '', 1), $tokenStream->next());
        $this->assertEquals(new Twig_Token(Twig_Token::TEXT_TYPE, '</html>', 1), $tokenStream->next());
        $this->assertEquals(new Twig_Token(Twig_Token::EOF_TYPE, '', 1), $tokenStream->getCurrent());
        //$tokenStream = $lexer->tokenize("{{'Hello'}}{% set test = 'sdf' %}");
    }

    public function testHowToTransformTwigTemplateIntoAst()
    {
        $stream = $this->twig->tokenize($this->templates['tpl3.html.twig'], 'tpl3.html.twig');
        // this single line allow to transform a stream of token into a Tree of nodes (AST).
        $nodes = $this->twig->parse($stream);

        //echo $nodes;
        $this->assertInstanceOf('Twig_Node_Module', $nodes);

        $this->assertEquals('tpl3.html.twig', $nodes->getAttribute('filename'));
        $this->assertInstanceOf('Twig_Node_Body', $nodes->getNode('body'));
        $this->assertEquals(null, $nodes->getNode('parent'), 'A Twig template could have a body OR a parent, but not either.');
        $this->assertInstanceOf('Twig_Node', $nodes->getNode('blocks'));
        $this->assertInstanceOf('Twig_Node', $nodes->getNode('macros'));
        //echo $nodes;
        // Allow to retrieve 
        $this->assertInstanceOf('Twig_Node_Body', $nodes->getNode('blocks')->getNode('nb1'));
        $this->assertInstanceOf('Twig_Node_Macro', $nodes->getNode('macros')->getNode('macro1'));
    }

    public function testHowToCountTheNumberOfNodeMadeInASingleTemplate()
    {
        $stream = $this->twig->tokenize($this->templates['tpl3.html.twig']);
        $nodes = $this->twig->parse($stream);
        $entryBody = $nodes->getNode('body');
        $nbSets = 0;
        $this->foundSetNodes($entryBody, $nbSets, '\Twig_Node_Set');

        $this->assertEquals(2, $nbSets);
    }

    private function foundSetNodes($node, &$nbSets, $nodeType)
    {
        if ($node === null) {
            return;
        }

        if ($node instanceof $nodeType) {
            $nbSets += 1;
        }

        // code which activate the recursivity
        if ($node->count() > 0) {
            foreach ($node as $n) {
                $this->foundSetNodes($n, $nbSets, $nodeType);
            }
        }
    }
}

class MyTagTokenParser extends \Twig_TokenParser
{
    public function parse(Twig_Token $token)
    {
        $this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(array($this, 'decideSpacelessEnd'), true);
        $this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);

        return new \Twig_Node_Spaceless($body, 0, $this->getTag());
    }

    public function decideSpacelessEnd(Twig_Token $token)
    {
        return $token->test('endmytag');
    }

    public function getTag()
    {
        return 'mytag';
    }
}
