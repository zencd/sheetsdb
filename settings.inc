<?php

/**
 * ID документа google sheets, где ведётся расписание
 * документ должен быть доступен на чтение анонимно по урлу
 */
const DOC_ID = '1XDv3LygpyRMmZe9rQZew0hUE4_HsFsQYC70D0t2HitY';

/**
 * через столько времени расписание пересоздаётся из google sheets
 * в остальное время отдаётся закэшированная версия
 * ед. изм.: минуты
 */
const REGEN_MINUTES = 0; // debug
//const REGEN_MINUTES = 60;

/**
 * эфир считается прошедшим если после его начала прошло столько времени
 * ед. изм.: часы
 */
const EVENT_LENGTH_HOURS = 4;

const TZ_STR = 'Europe/Moscow';
