/**
 * jQuery Magnifier Plugin
 * @author Dieter Orens dieter@dio5.com
 * 
 * @option Number lensWidth -  width of the lens 
 * @option Number lensHeight - height of the lens
 * @option Boolean link - makes clicking go to the large image (default:true)
 * @option Number delay - adds a delay to the appearing of the lens (default:0)
 * 
 */
(function($){
    $.extend($.fn,
    {
        magnify:function(options)
        {
            return this.each(function()
            {
                var magnifier =
                {
                    defaults:
                    {
                        lensWidth: 160,
                        lensHeight: 160,
                        link: true,
                        delay: 0
                    },
					
                    a: null,
                    $img: null,
                    $largeImage: null,
                    $lens: null,
                    $sensor: null,
                    $loader: null,
                    timeOut: null,
                    largeWidth: 0,
                    largeHeight: 0,
					
                    init:function(options)
                    {
                        magnifier.options = $.extend({}, magnifier.defaults, options);
                        magnifier.a = this;
                        magnifier.$img = $('img', this);
                        magnifier.setLargeImage();
                        magnifier.setLens();
                        magnifier.setSensor();
                        magnifier.setLoader();
                        magnifier.loadImage();
                        magnifier.addHandles();
                    },
					
                    setLargeImage:function()
                    {
                        magnifier.$largeImage = $(new Image());
                        magnifier.$largeImage.attr('src', magnifier.a.href).css('display', 'none');
                    },
					
                    setLens:function()
                    {
                        magnifier.$lens = $("<div id='dio-lens'></div>");
                        magnifier.$lens.css({
                            width: magnifier.options.lensWidth,
                            height: magnifier.options.lensHeight,
                            visibility: 'hidden',
                            overflow: 'hidden',
                            position: 'absolute',
                            left:0,
                            top:0
                        }).appendTo('body');
                    },
					
                    setSensor:function()
                    {
                        magnifier.$sensor = $("<div id='dio-sensor' style='position:absolute;'></div>");
                        $('body').append(magnifier.$sensor);
						
                        if (magnifier.options.link)
                        {
                            magnifier.$sensor.click(function(){
                                //window.location = magnifier.a.href
                                $.fn.colorbox({href:magnifier.a.href, open:true});
                            });
                        }
                    },
					
                    setLoader:function()
                    {
                        magnifier.$loader = $("<div id='dio-loader'>loading</div>").css({
                            width: magnifier.options.lensWidth,
                            height: magnifier.options.lensHeight
                            });
                        magnifier.$lens.append(magnifier.$loader);
                    },
					
                    loadImage:function()
                    {
						
                        magnifier.$largeImage.load(function(e)
                        {
                            magnifier.imgLoadCheck(magnifier.$largeImage[0], magnifier.loadCallback, magnifier.errorCallback, e);
                        });
                    },
					
                    imgLoadCheck:function(img, loadCallback, errorCallback)
                    {
                        if(img!=null)
                        {
                            function imgWatch()
                            {
                                if(img.complete)
                                {
                                    clearInterval(loadWatch);
                                    loadCallback();
                                }
                            }
                            var loadWatch = setInterval(imgWatch, 100);
                        }
                        else
                        {
                            errorCallback();
                        }
                    },
					
                    loadCallback:function()
                    {
                        magnifier.$lens.append(magnifier.$largeImage);
						
                        function moveWatch()
                        {
                            if(magnifier.$largeImage.width())
                            {
                                magnifier.largeWidth = magnifier.$largeImage.width();
                                magnifier.largeHeight = magnifier.$largeImage.height();
                            }
                            if (magnifier.largeWidth) {
                                magnifier.$loader.remove();
                                clearInterval(moveID);
                            }
                        }
                        var moveID = setInterval(moveWatch, 100);
                    },
					
                    errorCallback:function()
                    {
                        alert("large image could not be loaded");
                    },
					
                    addHandles:function()
                    {
                        magnifier.$sensor.css(
                        {
                            width: magnifier.$img.width() + "px",
                            height: magnifier.$img.height() + "px",
                            top: magnifier.$img.offset().top + "px",
                            left: magnifier.$img.offset().left + "px",
                            backgroundColor: "#fff",
                            opacity: "0"
                        })
                        .mousemove(function(e){
                            magnifier.handleMouseMove(e);
                        })
                        .mouseout(function(e){
                            magnifier.handleMouseOut(e);
                        });
                    },
										
                    handleMouseMove:function(e)
                    {
                        magnifier.$lens.css({
                            left: parseInt(e.pageX - (magnifier.options.lensWidth * .5)) + "px",
                            top: parseInt(e.pageY - (magnifier.options.lensHeight * .5)) + "px"
                        });
						

                        if (magnifier.options.delay)
                        {
                            if (!magnifier.timeOut) {
                                magnifier.timeOut = setTimeout(function(){
                                    magnifier.$lens.css('visibility', 'visible');
                                }, magnifier.options.delay);
                            }
                        }
                        else {
                            magnifier.$lens.css('visibility', 'visible');
                        }
						
                        if(magnifier.largeWidth){
                            magnifier.positionLargeImage(e);
                        }
						
                        magnifier.$lens.css('display', 'block');
                    },
					
                    positionLargeImage:function(e)
                    {
                        var scale = {};
				
                        scale.x = magnifier.largeWidth / magnifier.$img.width();
                        scale.y = magnifier.largeHeight / magnifier.$img.height();
					
                        var left = -scale.x * Math.abs((e.pageX - magnifier.$img.offset().left)) + magnifier.options.lensWidth / 2 + "px";
                        var top = -scale.y * Math.abs((e.pageY - magnifier.$img.offset().top)) + magnifier.options.lensHeight / 2 + "px";
										
                        magnifier.$largeImage.css(
                        {
                            position: 'absolute',
                            left: left,
                            top: top,
                            display:'block'
                        });
                    },
					
                    handleMouseOut: function(e)
                    {
                        if (magnifier.timeOut) {
                            clearTimeout(magnifier.timeOut);
                            magnifier.timeOut = null;
                        }
                        magnifier.$lens.css({
                            visibility: 'hidden',
                            display: 'none'
                        });
                    }
                };
				
                magnifier.init.call(this,options);
            });
        }
    });
})(jQuery);