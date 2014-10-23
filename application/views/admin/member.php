<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="utf-8">
	<title>소리너울 - SoundSwell</title>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script type="text/javascript" src="/resource/script/admin.js"></script>
    <link rel="stylesheet" type="text/css" href="/resource/css/style.css"/>
    <link rel="stylesheet" type="text/css" href="/resource/css/admin.css"/>
</head>
<body id="Member">
회원관리 패널
<ul>
    <li><a href="/admin/member/">회원 관리</a></li>
    <li><a href="/admin/login/">로그인 기록조회</a></li>
    <li><a href="/admin/record/">플레이 기록관리</a></li>
    <li><a href="/admin/ir/">IR 랭킹 조회</a></li>
    <li><a href="/admin/referer/">레퍼러 조회</a></li>
</ul>
<div class="borderAll">
    <table>
    <tr><th>고유번호</th><th>ID</th><th>닉네임</th><th>Email</th><th>등록일</th><th>권한</th><th>삭제여부</th><th>조작</th></tr>
    <?php foreach($memberList as $record){ ?>
    <tr>
        <td><?php echo number_format($record->nSeqNo);?></td>
        <td><?php echo $record->vcUserId;?></td>
        <td><?php echo $record->vcNickname;?></td>
        <td><?php echo $record->vcEmail;?></td>
        <td><?php echo $record->dtRegdate;?></td>
        <td class="Admin_<?php echo $record->nSeqNo;?>"><?php echo $record->emAdmin;?></td>
        <td><?php echo $record->emDel;?></td>
        <td class="<?php echo $record->nSeqNo;?>"><span class="passwd">비번변경</span> <span class="admin">권한관리</span> <a href="/admin/login/?seqNo=<?php echo $record->nSeqNo;?>">로그인조회</a> <?php if($record->emDel=="N"){ ?><span class="del">삭제</span><?php }else{ ?><span class="restore">복구</span><?php } ?></td>
    </tr>
    <?php } ?>
    </table>
    <div class="page">
    <?php
    $perpage = 20;
    $totalPage = ceil($total/$perpage);
    $nowPage = $page;
    $str = '';

    if($nowPage > 1){
        $startPage = floor(($nowPage-1)/10)*10+1;
    }else{
        $startPage = 1;
    }

    if( (floor(($nowPage-1)/10) +1)*10 < $totalPage ){
        $endPage = (floor(($nowPage-1)/10) +1) * 10;
    }else{
        $endPage = $totalPage;
    }

    if(10 <= $startPage ) $str .= ' <a href="/admin/member/'.($startPage-1).'">&lt;</a> &nbsp; ';
    else $str .= '&lt; &nbsp; ';

    for($a=$startPage; $a<=$endPage; $a++){
        if($a==$nowPage) $str .= ' <span style="font-weight:bold;">'.$a.'</span> &nbsp; ';
        else $str .= ' <a href="/admin/member/'.$a.'">'.$a.'</a> &nbsp; ';
    }

    if($endPage < $totalPage ) $str .= ' <a href="/admin/member/'.$endPage.'">&gt;</a> &nbsp; ';
    else $str .= '&gt; &nbsp; ';

    echo $str;
    ?>
    </div>
</div>
<div id="Permission">
설정할 권한 선택: <select><option value="">없음</option><option value="Admin">관리자</option></select> <span>확인</span> <span class="close">닫기</span>
</div>
<div id="Password">
변경될 비번 입력: <input type="password" /> <span>확인</span> <span class="close">닫기</span>
</div>
</body>
</html>