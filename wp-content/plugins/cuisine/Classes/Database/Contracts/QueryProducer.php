<?php

	namespace Cuisine\Database\Contracts;


	interface QueryProducer{

		public function getTable();
		public function getColumns();
		public function getCommands();

	}