$(function(){
  var playlist = [{
      title:"亡灵序曲11",
      artist:"魔兽世界11",
      mp3: '../../mp3/TheDawn.mp3',
      poster: "../../images/love/541438e7cd272.gif"
    },{
      title:"超好听德国童声",
      artist:"德国童声",
      mp3: '../../mp3/chenparty.mp3',
      poster: "../../images/love/yqB0erk.jpg"
    },{
      title:"第一装甲师进行曲",
	  artist:"德国",
      mp3: '../../mp3/deguo.mp3',
      poster: "../../images/love/540847298c9e0.gif"
  },{
      title:"超好听德国童声",
      artist:"德国童声",
      mp3: '../../mp3/chenparty.mp3',
      poster: "../../images/love/yqB0erk.jpg"
    },{
      title:"超好听德国童声",
      artist:"德国童声",
      mp3: '../../mp3/chenparty.mp3',
      poster: "../../images/love/yqB0erk.jpg"
    },{
      title:"超好听德国童声",
      artist:"德国童声",
      mp3: '../../mp3/chenparty.mp3',
      poster: "../../images/love/yqB0erk.jpg"
    },{
      title:"超好听德国童声",
      artist:"德国童声",
      mp3: '../../mp3/chenparty.mp3',
      poster: "../../images/love/yqB0erk.jpg"
    }];
  
  var cssSelector = {
    jPlayer: "#jquery_jplayer",
    cssSelectorAncestor:".music-player"
  };
  
  var options = {
    swfPath: "Jplayer.swf",
	solution: 'html, flash',
    supplied: "ogv, m4v, oga, mp3"
  };
  
  var myPlaylist = new jPlayerPlaylist(cssSelector, playlist, options);
  
});