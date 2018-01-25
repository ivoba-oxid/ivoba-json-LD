[{$smarty.block.parent}]
[{assign var="jsonLd" value=$oViewConf->getJsonLd()}]
[{if $jsonLd }]
<script type='application/ld+json'>
  [{$jsonLd}]
</script>
[{/if}]