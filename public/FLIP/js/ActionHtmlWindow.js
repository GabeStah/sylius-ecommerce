function ActionHtmlWindow(a){var b=this,c=function(a){var b=a.split(";"),c=b[0]||"";return{src:c,width:b[1]||600,height:b[2]||340}},d=function(a,b){document.all?a.style.filter="alpha(opacity="+b+")":a.style.opacity=b/100},e=c(a),f=document.createElement("div"),g=document.createElement("div"),h=document.createElement("div"),i=document.createElement("div"),j=document.createElement("iframe"),k=f.style,l=g.style,m=h.style,n=i.style,o=j.style;k.position="fixed",k.top="0",k.right="0",k.bottom="0",k.left="0",k.zIndex="1040",k.opacity=.5,k.backgroundColor="#000",d(f,50),l.position="fixed",l.top="0",l.right="0",l.bottom="0",l.left="0",l.zIndex="1040",l.padding="0",l.border="none",l.margin="0",l.overflow="auto",n.position="relative",n.display="block",n.backgroundColor="#fff",n.lineHeight="0px",n.margin="0 auto",n.width=e.width+"px",n.height=e.height+"px",o.position="relative",o.border="none",o.margin="0",o.padding="0",o.left="0px",o.top="0px",j.width="100%",j.height="100%",j.src=e.src,m.position="fixed",m.width="60px",m.height="60px",m.top="0px",m.right="0px",m.overflow="hidden",m.cursor="pointer",h.title="Close",h.innerHTML="<img src='files/extfiles/close_btn.png' style='width:100%;height:100%;'>";var p=new Image;p.onload=function(){h.innerHTML="<img src='files/extfiles/close_btn.png' style='width:100%;height:100%;'>"},p.onerror=function(){h.innerHTML="<img src='../files/extfiles/close_btn.png' style='width:100%;height:100%;'>"},p.src="files/extfiles/close_btn.png",g.appendChild(i),g.appendChild(h),i.appendChild(j);var q=document.getElementsByTagName("body")[0];q.appendChild(f),q.appendChild(g),g.onclick=function(){var a=document.getElementsByTagName("body")[0];a.removeChild(f),a.removeChild(g)},i.onclick=function(a){return a.preventDefault(),!1},h.onclick=function(a){a.stopPropagation(),a.preventDefault();var b=document.getElementsByTagName("body")[0];return b.removeChild(f),b.removeChild(g),window.FlipBookBuilder.onCloseHtml&&window.FlipBookBuilder.onCloseHtml(),!1};var r=Math.max(10,(g.clientHeight-e.height)/2)+"px";i.style.marginTop=r,i.style.marginBottom=r,setTimeout(function(){b.box=g,b.bg=f},0)}ActionHtmlWindow2.prototype={constructor:ActionHtmlWindow2,destroy:function(){var a=document.getElementsByTagName("body")[0];a.removeChild(this.bg),a.removeChild(this.box),window.FlipBookBuilder.onCloseHtml&&window.FlipBookBuilder.onCloseHtml()}};