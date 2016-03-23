(function(e){var t=Upfront.Settings&&Upfront.Settings.l10n?Upfront.Settings.l10n.global.views:Upfront.mainData.l10n.global.views;define(["scripts/upfront/bg-settings/mixins"],function(n){var r=Upfront.Views.Editor.Settings.Item.extend(_.extend({},n,{group:!1,initialize:function(e){var n=this,r=function(){var e=this.get_value();this.model.set_breakpoint_property(this.property_name,e)},s={transition:new Upfront.Views.Editor.Field.Select({model:this.model,label:t.slider_transition,property:"background_slider_transition",use_breakpoint_property:!0,default_value:"crossfade",icon_class:"upfront-region-field-icon",values:[{label:t.slide_down,value:"slide-down",icon:"bg-slider-slide-down"},{label:t.slide_up,value:"slide-up",icon:"bg-slider-slide-up"},{label:t.slide_left,value:"slide-left",icon:"bg-slider-slide-left"},{label:t.slide_right,value:"slide-right",icon:"bg-slider-slide-right"},{label:t.crossfade,value:"crossfade",icon:"bg-slider-crossfade"}],change:r,rendered:function(){this.$el.addClass("uf-bgsettings-slider-transition")}}),rotate:new Upfront.Views.Editor.Field.Checkboxes({model:this.model,property:"background_slider_rotate",use_breakpoint_property:!0,default_value:!0,layout:"horizontal-inline",multiple:!1,values:[{label:t.autorotate_each+" ",value:!0}],change:function(){var e=this.get_value();this.property.set({value:e?!0:!1})},rendered:function(){this.$el.addClass("uf-bgsettings-slider-rotate")}}),rotate_time:new Upfront.Views.Editor.Field.Number({model:this.model,property:"background_slider_rotate_time",use_breakpoint_property:!0,default_value:5,min:1,max:60,step:1,suffix:"sec",change:r,rendered:function(){this.$el.addClass("uf-bgsettings-slider-time")}}),control:new Upfront.Views.Editor.Field.Radios({model:this.model,property:"background_slider_control",use_breakpoint_property:!0,default_value:"always",layout:"horizontal-inline",values:[{label:t.always_show_ctrl,value:"always"},{label:t.show_ctrl_hover,value:"hover"}],change:r,rendered:function(){this.$el.addClass("uf-bgsettings-slider-control")}})};this.$el.addClass("uf-bgsettings-item uf-bgsettings-slideritem"),e.fields=_.map(s,function(e){return e}),this.slides_item=new i({model:this.model,title:t.slides_order+":"}),_.isUndefined(e.slides_item_el)?this.on("panel:set",function(){n.panel.settings.push(n.slides_item),n.slides_item.panel=n.panel,n.slides_item.trigger("panel:set")}):(this.slides_item.render(),e.slides_item_el.append(this.slides_item.$el)),this.on("show",function(){var e=this.model.get_breakpoint_property_value("background_slider_images",!0);e||n.upload_slider_images(),n.slides_item.trigger("show")}),this.on("hide",function(){n.slides_item.trigger("hide")}),this.bind_toggles(),this.constructor.__super__.initialize.call(this,e)},upload_slider_images:function(){var e=this;Upfront.Views.Editor.ImageSelector.open({multiple:!0}).done(function(t){var n=[];_.each(t,function(e,t){n.push(t)}),e.model.set_breakpoint_property("background_slider_images",n),e.slides_item.update_slider_slides(),Upfront.Views.Editor.ImageSelector.close()})}})),i=Upfront.Views.Editor.Settings.Item.extend(_.extend({},n,{initialize:function(t){var n=this;this.$el.on("click",".upfront-region-bg-slider-add-image",function(e){e.preventDefault(),e.stopPropagation(),Upfront.Views.Editor.ImageSelector.open({multiple:!0}).done(function(e){var t=_.clone(n.model.get_breakpoint_property_value("background_slider_images",!0)||[]);_.each(e,function(e,n){t.push(n)}),n.model.set_breakpoint_property("background_slider_images",t),Upfront.Views.Editor.ImageSelector.close(),n.update_slider_slides()})}),this.$el.on("click",".upfront-region-bg-slider-delete-image",function(t){t.preventDefault(),t.stopPropagation();var r=e(this).closest(".upfront-region-bg-slider-image"),i=r.data("image-id"),s=n.model.get_breakpoint_property_value("background_slider_images",!0);_.isString(i)&&i.match(/^[0-9]+$/)&&(i=parseInt(i,10)),s=_.without(s,i),n.model.set_breakpoint_property("background_slider_images",s),r.remove(),n.update_slider_slides()}),this.on("show",function(){n.update_slider_slides()}),this.$el.addClass("uf-bgsettings-item uf-bgsettings-slider-slidesitem"),this.bind_toggles(),this.constructor.__super__.initialize.call(this,t)},update_slider_slides:function(){var n=this,r=n.model.get_breakpoint_property_value("background_slider_images",!0),i=e('<div class="upfront-region-bg-slider-add-image upfront-icon upfront-icon-region-add-slide">'+t.add_slide+"</div>"),s=this.$el.find(".upfront-settings-item-content");s.html(""),r.length>0?Upfront.Views.Editor.ImageEditor.getImageData(r).done(function(t){var o=t.data.images;_.each(r,function(t){var n=_.isNumber(t)||t.match(/^\d+$/)?o[t]:_.find(o,function(e){return e.full[0].split(/[\\/]/).pop()==t.split(/[\\/]/).pop()}),r=e('<div class="upfront-region-bg-slider-image" />');r.data("image-id",t),typeof n.thumbnail!="undefined"&&r.css({background:'url("'+n.thumbnail[0]+'") no-repeat 50% 50%',backgroundSize:"100% auto"}),r.append('<span href="#" class="upfront-region-bg-slider-delete-image">&times;</span>'),s.append(r)}),s.hasClass("ui-sortable")?s.sortable("refresh"):s.sortable({items:">  .upfront-region-bg-slider-image",update:function(){var t=[];s.find(".upfront-region-bg-slider-image").each(function(){var n=e(this).data("image-id");n&&t.push(n)}),n.model.set_breakpoint_property("background_slider_images",t)}}),s.append(i)}):s.append(i)}}));return r})})(jQuery);