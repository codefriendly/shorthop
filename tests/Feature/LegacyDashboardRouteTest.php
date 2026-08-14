<?php

test('the legacy dashboard route is gone', function () {
    $this->get('/dashboard')->assertNotFound();
});
