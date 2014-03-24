var noobSlide=new Class({initialize:function(b){this.minVisible=b.minVisible||1;this.marginR=b.marginR||0;this.items=b.items;this.mode=b.mode||"horizontal";this.modes={horizontal:["left","width"],vertical:["top","height"]};this.size=b.size||240;this.mask=b.mask||null;this.box=b.box.setStyle(this.modes[this.mode][1],(this.size*this.items.length)+"px");this.button_event=b.button_event||"click";this.handle_event=b.handle_event||"click";this.onWalk=b.onWalk||null;this.currentIndex=0;this.lastIndex=null;this.previousIndex=null;this.nextIndex=null;this.interval=b.interval||5000;this.autoPlay=b.autoPlay||false;this._auto=null;this.handles=b.handles||null;this.page_handles=b.page_handles||null;if(this.handles){this.addHandleButtons(this.handles)}this.buttons={previous:[],next:[],play:[],playback:[],stop:[]};if(b.buttons){for(var a in b.buttons){this.addActionButtons(a,$type(b.buttons[a])=="array"?b.buttons[a]:[b.buttons[a]])}}this.fx=new Fx.Style(this.box,this.modes[this.mode][0],b.fxOptions||{duration:500,wait:false});this.box.setStyle(this.modes[this.mode][0],(this.size*-(b.startItem||0))+"px");if(b.autoPlay){this.play(this.interval,"next",true)}},addHandleButtons:function(b){for(var a=0;a<b.length;a++){b[a].addEvent(this.handle_event,this.walk.pass([a,true],this))}},addActionButtons:function(c,b){for(var a=0;a<b.length;a++){switch(c){case"previous":b[a].addEvent(this.button_event,this.previous.pass([true],this));break;case"next":b[a].addEvent(this.button_event,this.next.pass([true],this));break;case"play":b[a].addEvent(this.button_event,this.play.pass([this.interval,"next",false],this));break;case"playback":b[a].addEvent(this.button_event,this.play.pass([this.interval,"previous",false],this));break;case"stop":b[a].addEvent(this.button_event,this.stop.create({bind:this}));break}this.buttons[c].push(b[a])}},previous:function(a){this.walk((this.currentIndex>0?this.currentIndex-1:this.items.length-1),a)},next:function(a){this.walk((this.currentIndex<this.items.length-1?this.currentIndex+1:0),a)},play:function(a,c,b){this.stop();if(!b){this[c](false)}this._auto=this[c].periodical(a,this,false)},stop:function(){$clear(this._auto)},walk:function(d,c){d=d<this.items.length?d:this.items.length-1;if(1){if(this.mask&&this.mode=="horizontal"){minVisible=(this.mask.clientWidth+this.marginR)/this.size;this.minVisible=parseInt(minVisible)}if(!this.minVisible){this.minVisible=1}this.lastIndex=this.lastIndex||0;this.currentIndex=d;this.previousIndex=this.currentIndex+(this.currentIndex>0?-1:this.items.length-1);this.nextIndex=this.currentIndex+(this.currentIndex<this.items.length-1?1:1-this.items.length);if(this.currentIndex>=this.lastIndex&&this.currentIndex<=this.lastIndex+this.minVisible-1){var b=this.lastIndex}else{if(this.currentIndex+this.minVisible>=this.items.length){var b=this.lastIndex<this.items.length-this.minVisible?this.currentIndex-(this.minVisible-1):this.items.length-this.minVisible}else{var b=this.lastIndex<this.currentIndex?this.currentIndex-(this.minVisible-1):this.currentIndex}}this.lastIndex=b;var a=false;if(c){this.stop()}if(a){this.fx.cancel().set((this.size*-b)+"px")}else{this.fx.start(this.size*-b)}if(c&&this.autoPlay){this.play(this.interval,"next",true)}if(this.onWalk){this.onWalk((this.items[this.currentIndex]||null),(this.handles&&this.handles[this.currentIndex]?this.handles[this.currentIndex]:null))}}}});