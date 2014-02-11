<?php if (!defined('APPLICATION')) exit();

function WriteDiscussion($Discussion, &$Sender, &$Session, $Alt2) {
   static $Alt = FALSE;
   $CssClass = 'Item';
   $CssClass .= $Discussion->Bookmarked == '1' ? ' Bookmarked' : '';
   $CssClass .= $Alt ? ' Alt ' : '';
   $Alt = !$Alt;
   $CssClass .= $Discussion->Announce == '1' ? ' announcement_discussion' : '';
   $CssClass .= $Discussion->Dismissed == '1' ? ' dismissed_discussion' : '';
   $CssClass .= $Discussion->InsertUserID == $Session->UserID ? ' Mine' : '';
   $CssClass .= ($Discussion->CountUnreadComments > 0 && $Session->IsValid()) ? ' New' : '';
   $DiscussionUrl = '/discussion/'.$Discussion->DiscussionID.'/'.Gdn_Format::Url($Discussion->Name).($Discussion->CountCommentWatch > 0 && C('Vanilla.Comments.AutoOffset') && $Session->UserID > 0 ? '/#Item_'.$Discussion->CountCommentWatch : '');
   $Sender->EventArguments['DiscussionUrl'] = &$DiscussionUrl;
   $Sender->EventArguments['Discussion'] = &$Discussion;
   $Sender->EventArguments['CssClass'] = &$CssClass;
   $First = UserBuilder($Discussion, 'First');
   $Last = UserBuilder($Discussion, 'Last');
   
   $Sender->FireEvent('BeforeDiscussionName');
   
   $DiscussionName = $Discussion->Name;
   if ($DiscussionName == '')
      $DiscussionName = T('Blank Discussion Topic');
      
   $Sender->EventArguments['DiscussionName'] = &$DiscussionName;

   static $FirstDiscussion = TRUE;
   if (!$FirstDiscussion)
      $Sender->FireEvent('BetweenDiscussion');
   else
      $FirstDiscussion = FALSE;
?>
<li class="<?php echo $CssClass; ?>">
   <div class="ItemContent Discussion">
      <div class="discussion_topline">
         <?php
         if (!property_exists($Sender, 'CanEditDiscussions'))
            $Sender->CanEditDiscussions = GetValue('PermsDiscussionsEdit', CategoryModel::Categories($Discussion->CategoryID)) && C('Vanilla.AdminCheckboxes.Use');;

         $Sender->FireEvent('BeforeDiscussionContent');

         WriteOptions($Discussion, $Sender, $Session);
         ?>
        <?php echo Anchor($DiscussionName, $DiscussionUrl, 'Title'); ?>
        <?php $Sender->FireEvent('AfterDiscussionTitle'); ?>
        <div class="Meta">
           <span class="CommentCount"><?php printf(Plural($Discussion->CountComments, '%s comment', '%s comments'), $Discussion->CountComments); ?></span>
           <br />
           <?php if ($Discussion->Announce == '1') { ?>
           <span class="Announcement"><?php echo T('Announcement'); ?></span>
           <?php } ?>
           <?php if ($Discussion->Closed == '1') { ?>
           <span class="Closed"><?php echo T('Closed'); ?></span>
           <?php } ?>
         </div>
      </div>
      <div class="Meta">
         <?php $Sender->FireEvent('BeforeDiscussionMeta'); ?>
         <?php
            if ($Session->IsValid() && $Discussion->CountUnreadComments > 0)
               echo '<span class="new_message">'.Plural($Discussion->CountUnreadComments, '%s New', '%s New Plural').'</span>';

            $Sender->FireEvent('AfterCountMeta');

            if ($Discussion->LastCommentID != '') {
               echo '<span class="LastCommentBy">'.sprintf(T('Most recent by %1$s '), UserAnchor($Last)).'</span> - ';
               echo '<span class="LastCommentDate">'.Gdn_Format::Date($Discussion->LastDate).'</span> - ';
            } else {
               echo '<span class="LastCommentBy">'.sprintf(T('Started by %1$s'), UserAnchor($First)).'</span> - ';
               echo '<span class="LastCommentDate">'.Gdn_Format::Date($Discussion->FirstDate);
               
               if ($Source = GetValue('Source', $Discussion)) {
                  echo ' '.sprintf(T('via %s'), T($Source.' Source', $Source));
               }
               
               echo '</span> - ';
            }
         
            if (C('Vanilla.Categories.Use') && $Discussion->CategoryUrlCode != '')
               echo Wrap(Anchor($Discussion->Category, '/categories/'.rawurlencode($Discussion->CategoryUrlCode), 'Category'));
               
            $Sender->FireEvent('DiscussionMeta');
         ?>
      </div>
   </div>
</li>
<?php
}

function WriteFilterTabs(&$Sender) {
   $Session = Gdn::Session();
   $Title = property_exists($Sender, 'Category') ? GetValue('Name', $Sender->Category, '') : '';
   if ($Title == '')
      $Title = T('All Discussions');
      
   $Bookmarked = T('My Bookmarks');
   $MyDiscussions = T('My Discussions');
   $MyDrafts = T('My Drafts');
   $CountBookmarks = 0;
   $CountDiscussions = 0;
   $CountDrafts = 0;
   if ($Session->IsValid()) {
      $CountBookmarks = $Session->User->CountBookmarks;
      $CountDiscussions = $Session->User->CountDiscussions;
      $CountDrafts = $Session->User->CountDrafts;
   }
   if ($CountBookmarks === NULL) {
      $Bookmarked .= '<span class="Popin" rel="'.Url('/discussions/UserBookmarkCount').'">-</span>';
   } elseif (is_numeric($CountBookmarks) && $CountBookmarks > 0)
      $Bookmarked .= '<span class="Count">'.$CountBookmarks.'</span>';

   if (is_numeric($CountDiscussions) && $CountDiscussions > 0)
      $MyDiscussions .= '<span class="Count">'.$CountDiscussions.'</span>';

   if (is_numeric($CountDrafts) && $CountDrafts > 0)
      $MyDrafts .= '<span class="Count">'.$CountDrafts.'</span>';
      
   ?>
<div class=" Tabs DiscussionsTabs">
   <ul id="context-menu">

      <?php if ($CountBookmarks > 0 || $Sender->RequestMethod == 'bookmarked') { ?>
      <li class="context-item" "<?php echo $Sender->RequestMethod == 'bookmarked' ? ' class="Active"' : ''; ?>><?php echo Anchor($Bookmarked, '/discussions/bookmarked', 'MyBookmarks TabLink'); ?></li>
      <?php
         $Sender->FireEvent('AfterBookmarksTab');
      }
      if ($CountDiscussions > 0 || $Sender->RequestMethod == 'mine') {
      ?>
      <li class="context-item" <?php echo $Sender->RequestMethod == 'mine' ? ' class="Active"' : ''; ?>><?php echo Anchor($MyDiscussions, '/discussions/mine', 'MyDiscussions TabLink'); ?></li>
      <?php
      }
      if ($CountDrafts > 0 || $Sender->ControllerName == 'draftscontroller') {
      ?>
      <li class="contect-item" <?php echo $Sender->ControllerName == 'draftscontroller' ? ' class="Active"' : ''; ?>><?php echo Anchor($MyDrafts, '/drafts', 'MyDrafts TabLink'); ?></li>
      <?php
      }
      $Sender->FireEvent('AfterDiscussionTabs');
       $Breadcrumbs = Gdn::Controller()->Data('Breadcrumbs');
       if ($Breadcrumbs) {
          echo '<li class="SubTab Breadcrumbs">';
          $First = TRUE;
          foreach ($Breadcrumbs as $Breadcrumb) {
             if ($First) {
                $Class = 'Breadcrumb FirstCrumb';
                $First = FALSE;
             } else {
                $Class = 'Breadcrumb';
                echo '<span class="Crumb"> &raquo; </span>';
             }
             
             echo '<span class="'.$Class.'">', Anchor(Gdn_Format::Text($Breadcrumb['Name']), $Breadcrumb['Url']), '</span>';
          }
          $Sender->FireEvent('AfterBreadcrumbs');
          echo '</li>';
       }
      ?>
   </ul>
   <?php
   if (!property_exists($Sender, 'CanEditDiscussions'))
      $Sender->CanEditDiscussions = $Session->CheckPermission('Vanilla.Discussions.Edit', TRUE, 'Category', 'any') && C('Vanilla.AdminCheckboxes.Use');
   
   if ($Sender->CanEditDiscussions) {
   ?>
   <span class="AdminCheck">
      <input type="checkbox" name="Toggle" />
   </span>
   <?php } ?>
</div>
   <?php
}

/**
 * Render options that the user has for this discussion.
 */
function WriteOptions($Discussion, &$Sender, &$Session) {
   if ($Session->IsValid() && $Sender->ShowOptions) {
      echo '<div class="Options">';
      $Sender->Options = '';
      
      // Dismiss an announcement
      if (C('Vanilla.Discussions.Dismiss', 1) && $Discussion->Announce == '1' && $Discussion->Dismissed != '1')
         $Sender->Options .= '<li>'.Anchor(T('Dismiss'), 'vanilla/discussion/dismissannouncement/'.$Discussion->DiscussionID.'/'.$Session->TransientKey(), 'DismissAnnouncement') . '</li>';
      
      // Edit discussion
      if ($Discussion->FirstUserID == $Session->UserID || $Session->CheckPermission('Vanilla.Discussions.Edit', TRUE, 'Category', $Discussion->PermissionCategoryID))
         $Sender->Options .= '<li>'.Anchor(T('Edit'), 'vanilla/post/editdiscussion/'.$Discussion->DiscussionID, 'EditDiscussion') . '</li>';

      // Announce discussion
      if ($Session->CheckPermission('Vanilla.Discussions.Announce', TRUE, 'Category', $Discussion->PermissionCategoryID))
         $Sender->Options .= '<li>'.Anchor(T($Discussion->Announce == '1' ? 'Unannounce' : 'Announce'), 'vanilla/discussion/announce/'.$Discussion->DiscussionID.'/'.$Session->TransientKey().'?Target='.urlencode($Sender->SelfUrl), 'AnnounceDiscussion') . '</li>';

      // Sink discussion
      if ($Session->CheckPermission('Vanilla.Discussions.Sink', TRUE, 'Category', $Discussion->PermissionCategoryID))
         $Sender->Options .= '<li>'.Anchor(T($Discussion->Sink == '1' ? 'Unsink' : 'Sink'), 'vanilla/discussion/sink/'.$Discussion->DiscussionID.'/'.$Session->TransientKey().'?Target='.urlencode($Sender->SelfUrl), 'SinkDiscussion') . '</li>';

      // Close discussion
      if ($Session->CheckPermission('Vanilla.Discussions.Close', TRUE, 'Category', $Discussion->PermissionCategoryID))
         $Sender->Options .= '<li>'.Anchor(T($Discussion->Closed == '1' ? 'Reopen' : 'Close'), 'vanilla/discussion/close/'.$Discussion->DiscussionID.'/'.$Session->TransientKey().'?Target='.urlencode($Sender->SelfUrl), 'CloseDiscussion') . '</li>';
      
      // Delete discussion
      if ($Session->CheckPermission('Vanilla.Discussions.Delete', TRUE, 'Category', $Discussion->PermissionCategoryID))
         $Sender->Options .= '<li>'.Anchor(T('Delete'), 'vanilla/discussion/delete/'.$Discussion->DiscussionID.'/'.$Session->TransientKey().'?Target='.urlencode($Sender->SelfUrl), 'DeleteDiscussion') . '</li>';
      
      // Allow plugins to add options
      $Sender->FireEvent('DiscussionOptions');
      
      if ($Sender->Options != '') {
      ?>
         <div class="ToggleFlyout OptionsMenu">
            <div class="MenuTitle"><?php echo T('Options'); ?></div>
            <ul class="Flyout MenuItems">
               <?php echo $Sender->Options; ?>
            </ul>
         </div>
      <?php
      }
      // Admin check.
      if ($Sender->CanEditDiscussions) {
         if (!property_exists($Sender, 'CheckedDiscussions')) {
            $Sender->CheckedDiscussions = (array)$Session->GetAttribute('CheckedDiscussions', array());
            if (!is_array($Sender->CheckedDiscussions))
               $Sender->CheckedDiscussions = array();
         }

         $ItemSelected = in_array($Discussion->DiscussionID, $Sender->CheckedDiscussions);
         echo '<span class="AdminCheck"><input type="checkbox" name="DiscussionID[]" value="'.$Discussion->DiscussionID.'"'.($ItemSelected?' checked="checked"':'').' /></span>';
      }

      /*
      $Title = T($Discussion->Bookmarked == '1' ? 'Unbookmark' : 'Bookmark');
      echo Anchor(
         '<span class="Star">'
            .Img('applications/dashboard/design/images/pixel.png', array('alt' => $Title))
         .'</span>',
         '/vanilla/discussion/bookmark/'.$Discussion->DiscussionID.'/'.$Session->TransientKey().'?Target='.urlencode($Sender->SelfUrl),
         'Bookmark' . ($Discussion->Bookmarked == '1' ? ' Bookmarked' : ''),
         array('title' => $Title)
      );
    */
      
      echo '</div>';
   }
}
