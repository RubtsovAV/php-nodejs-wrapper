<?php

namespace Kurbits\JavaScript;

final class JavaScriptCoreRunner extends ExternalRunner
{
    protected $commands = ['/System/Library/Frameworks/JavaScriptCore.framework/Versions/A/Resources/jsc'];
}