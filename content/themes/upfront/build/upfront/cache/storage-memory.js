upfrontrjs.define(["scripts/upfront/cache/storage-stub"],function(t){return _.extend({},t,{get_storage:function(){return window[t.get_storage_id()]||{}},set_storage:function(e){return window[t.get_storage_id()]=e,!0}})});