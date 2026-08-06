<?php

use App\Http\Controllers\API\v1\FaceDetectionController;

Route::prefix('/face-detections')
    ->name('.faceDetections')
    ->group(function () {
        Route::post('/batch', [FaceDetectionController::class, 'batch'])
            ->middleware('auth.kurozora')
            ->name('.batch');
    });
