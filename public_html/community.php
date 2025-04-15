<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$title = 'Community';
$subtitle = 'Find out who is involved in the sanger-tol project';
$md_github_url = 'https://github.com/sanger-tol/pipelines-website/blob/main/sanger-tol-partners.yaml';
$import_leaflet = true;
include '../includes/header.php';
?>

<h1>Introduction</h1>
<p>sanger-tol is by design a collaborative effort, and would not exist if it were not for the efforts of many dedicated contributors.</p>
<ul>
    <li><a href="#contributors">Contributors</a></li>
    <li><a href="#organisations">Organisations</a></li>
    <li><a href="#initiatives">Projects we are involved with</a></li>
</ul>

<?php echo _h1('Contributors'); ?>
<p>The sanger-tol pipelines and community is driven by many individuals, listed below. This list updates automatically.</p>
<p>Want to see who's working on what? See the <a href="/stats#contributor_leaderboard">contributor leaderboard</a> on the Statistics page.</p>
<p class="pt-3">
    <?php
    $stats_json_fn = dirname(dirname(__FILE__)) . '/nfcore_stats.json';
    $stats_json = json_decode(file_get_contents($stats_json_fn));
    $contributors = [];
    foreach (['pipelines', 'core_repos'] as $repo_type) {
        foreach ($stats_json->{$repo_type} as $repo) {
            foreach ($repo->contributors as $contributor) {
                $contributors[$contributor->author->login] = $contributor->author;
            }
        }
    }
    // Random order!
    $logins = array_keys($contributors);
    shuffle($logins);
    foreach ($logins as $login) {
        $author = $contributors[$login];
        echo '<a title="@' .
            $author->login .
            '" href="' .
            $author->html_url .
            '" target="_blank" data-bs-toggle="tooltip"><img src="' .
            $author->avatar_url .
            '" class="border rounded-circle me-1 mb-1" width="50" height="50"></a>';
    }
    ?>
</p>

<?php echo _h1('Partners'); ?>
<p>Tree of Life programme at Wellcome Sanger Institute works with several partners across the globe to deliver its goal to sequence all eukaryotic life on the planet. Some of these organisations are listed below, along with a key person who you can contact for advice.</p>
<blockquote>Is your group missing? Please submit a pull request to add yourself! It's just a few lines in a <a href="https://github.com/sanger-tol/pipelines-website/blob/main/sanger-tol-partners.yaml">simple YAML file.</a></blockquote>

<div class="card contributors-map-card">
    <div class="card-body" id="contributors-map"></div>
</div>
<div class="row row-cols-1 row-cols-md-2 g-4">

    <?php
    // Parse YAML contributors file
    require '../vendor/autoload.php';

    use Spyc;

    $locations = [];
    $contributors = spyc_load_file('../sanger-tol-partners.yaml');
    $contributors_html = '';
    foreach ($contributors['contributors'] as $idx => $c) {
        // Start card div
        $contributors_html .= '<div class="col"><div class="card contributor h-100"><div class="card-body">';
        // Header, title
        $img_path = '';
        if (array_key_exists('image_fn', $c)) {
            // Dark theme
            $hide_dark = '';
            $dark_img_path = 'assets/img/contributors-white/' . $c['image_fn'];
            if ($c['image_fn'] and file_exists($dark_img_path)) {
                $contributors_html .=
                    '<img class="contributor_logo hide-light" title="' .
                    $c['full_name'] .
                    '" src="' .
                    $dark_img_path .
                    '">';
                $hide_dark = 'hide-dark';
            }
            // Normal, light theme
            $img_path = 'assets/img/contributors-colour/' . $c['image_fn'];
            if ($c['image_fn'] and file_exists($img_path)) {
                $contributors_html .=
                    '<img class="contributor_logo ' .
                    $hide_dark .
                    '" title="' .
                    $c['full_name'] .
                    '" src="' .
                    $img_path .
                    '">';
            } else {
                $img_path = '';
            }
        }
        $card_id = $idx;
        if (array_key_exists('full_name', $c)) {
            $card_id = preg_replace('/[^a-z]+/', '-', strtolower($c['full_name']));
            $contributors_html .= '<h5 class="card-title" id="' . $card_id . '">';
            if (array_key_exists('url', $c)) {
                $contributors_html .= ' <a href="' . $c['url'] . '" target="_blank">';
            }
            $contributors_html .= $c['full_name'];
            if (array_key_exists('url', $c)) {
                $contributors_html .= ' </a>';
            }
            $contributors_html .= '</h5>';
        }
        if (array_key_exists('affiliation', $c)) {
            $contributors_html .= '<h6 class="card-subtitle mb-2 text-muted">';
            if (array_key_exists('affiliation_url', $c)) {
                $contributors_html .= '<a href="' . $c['affiliation_url'] . '" target="_blank">';
            }
            $contributors_html .= $c['affiliation'];
            if (array_key_exists('affiliation_url', $c)) {
                $contributors_html .= '</a>';
            }
            $contributors_html .= '</h6>';
        }
        // Description
        if (array_key_exists('description', $c)) {
            $contributors_html .= '<p class="card-text small text-muted">' . $c['description'] . '</p> ';
        }
        // Contact person
        $contributors_html .= '<div class="contributor_contact">';
        if (array_key_exists('contact_email', $c)) {
            $contributors_html .=
                '<a href="mailto:' .
                $c['contact_email'] .
                '" class="badge bg-light text-dark fw-normal" data-bs-toggle="tooltip" title="Primary contact: ' .
                $c['contact_email'] .
                '"><i class="far fa-envelope"></i> ';
            if (array_key_exists('contact', $c)) {
                $contributors_html .= $c['contact'];
            } else {
                $contributors_html .= $c['contact_email'];
            }
            $contributors_html .= '</a> ';
        } elseif (array_key_exists('contact', $c)) {
            $contributors_html .= '<span class="badge bg-light text-dark fw-normal">' . $c['contact'] . '</span> ';
        }
        if (array_key_exists('contact_github', $c)) {
            $contributors_html .=
                '<a href="https://github.com/' .
                trim($c['contact_github'], '@') .
                '/" target="_blank" class="badge bg-light text-dark fw-normal" data-bs-toggle="tooltip" title="Primary contact: GitHub @' .
                trim($c['contact_github'], '@') .
                '"><i class="fab fa-github"></i> ' .
                trim($c['contact_github'], '@') .
                '</a> ';
        }
        if (array_key_exists('twitter', $c)) {
            $contributors_html .=
                '<a href="https://twitter.com/' .
                trim($c['twitter'], '@') .
                '/" target="_blank" class="badge bg-light text-dark fw-normal" data-bs-toggle="tooltip" title="Institutional twitter: @' .
                trim($c['twitter'], '@') .
                '"><i class="fab fa-twitter"></i> @' .
                trim($c['twitter'], '@') .
                '</a> ';
        }
        $contributors_html .= '</div>';
        // Close card div
        $contributors_html .= '</div></div></div>';

        // Location JSON
        if (array_key_exists('location', $c)) {
            $location['location'] = $c['location'];
            $location['full_name'] = array_key_exists('full_name', $c) ? $c['full_name'] : '';
            $location['card_id'] = $card_id;
            if ($img_path) {
                $location['image'] =
                    '<br><a href="#' .
                    $card_id .
                    '"><img class="contributor_map_logo" title="' .
                    $location['full_name'] .
                    '" src="' .
                    $img_path .
                    '"></a>';
            } else {
                $location['image'] = '';
            }
            array_push($locations, $location);
        }
    }

    echo $contributors_html;
    ?>

</div>
<script type="text/javascript">
    var locations = <?php echo json_encode($locations, JSON_PRETTY_PRINT); ?>;

    $(function() {
        var map = L.map('contributors-map', {
            zoom: 2
        });
        var greenIcon = new L.Icon({
            iconUrl: 'assets/img/marker-icon-2x-green.png',
            shadowUrl: 'assets/img/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var latlngs = [];
        locations.forEach(function(marker) {
            if (marker != null) {
                L.marker(marker.location, {
                    icon: greenIcon
                }).addTo(map).bindPopup('<a href="#' + marker.card_id + '">' + marker.full_name + '</a>' + marker.image);
                latlngs.push(marker.location);
            }
        });
        map.fitBounds(latlngs);
    });
</script>

<h1 id="initiatives">Projects we are involved with<a href="#initiatives" class="header-link"><span class="fas fa-link" aria-hidden="true"></span></a></h1>
<p>Tree of Life programme at Wellcome Sanger Institute is part of several biodiversity projects. You can see an overview of these below.</p>

<h3 id="dtol_testimonial">
    <img width="170px" src="/assets/img/dtol.png"
        class="float-end ps-4 darkmode-image" />
    Darwin Tree of Life
    <a href="#dtol_testimonial" class="header-link"><span class="fas fa-link" aria-hidden="true"></span></a>
</h3>
<p>The <a href="https://www.darwintreeoflife.org" target="_blank">Darwin Tree of Life (DToL)</a>
    project aims to sequence the genomes of 70,000 species of eukaryotic organisms in Britain and Ireland. 
    It is a collaboration between biodiversity, genomics and analysis partners that is transforming the way we do biology, 
    conservation and biotechnology.
<br/>
The Darwin Tree of Life Project is one of several initiatives across the globe working towards the ultimate goal of sequencing all complex life on Earth.
We are focussing on the organisms that live in and around Britain and Ireland because they constitute what is probably the best known and most deeply studied biota in the world, explored during centuries of observation and research.
</p>
<br/>

<h3 id="vgp_testimonial">
    <img width="170px" src="/assets/img/VGP_Tag_RGB_newLogo_72dpi_Dots_EDIT.png"
        class="float-end ps-4 darkmode-image" />
    Vertebrate Genomes Project
    <a href="#vgp_testimonial" class="header-link"><span class="fas fa-link" aria-hidden="true"></span></a>
</h3>
<p>The <a href="https://vertebrategenomesproject.org/" target="_blank">Vertebrate Genomes Project (VGP)</a>,
    a project of the G10K Consortium, aims to generate near error-free reference genome assemblies of ~70,000 extant vertebrate species.
<br/>
Phase 1 of the vgp will generate near error-free reference genomes of 260 species representing all vertebrate orders with a divergence time of ~50 million years ago (mya) or greater from their most recent common ordinal ancestor, including human and some species on the brink of extinction. We will sequence the heterogametic sex (when it exists) so that both sex chromosomes can be assembled for each species.
    </p>
<br/>

<h3 id="psyche_testimonial">
    <img width="170px" src="/assets/img/psyche_goddess_logo.png"
        class="float-end ps-4 darkmode-image" />
    Project Psyche
    <a href="#psyche_testimonial" class="header-link"><span class="fas fa-link" aria-hidden="true"></span></a>
</h3>
<p>
<i>Lepidoptera</i>, i.e. butterflies and moths, are vital components of the global ecosystem.
<a href="https://projectpsyche.org/" target="_blank">Project Psyche</a>
is a pan-European research project established to sequence the genomes of all butterflies and moths of Europe; helping to conserve, protect and drive innovation.
<i>Psyche</i> is named after the Greek goddess of the soul – who was frequently depicted with butterfly wings and is renowned for her beauty.
<br/>
Building on the Tree of Life Programme's expertise on similar biodiversity genomics projects, Project Psyche is expected to complete the 11,000 genomes in five years.
    </p>
<br/>

<h3 id="erga_testimonial">
    <img width="170px" src="/assets/img/e31d74_0696de4131f546f3bd52a4bf33a09c8a~mv2.png"
        class="float-end ps-4 darkmode-image" />
    European Reference Genome Atlas
    <a href="#erga_testimonial" class="header-link"><span class="fas fa-link" aria-hidden="true"></span></a>
</h3>
<p>The <a href="https://www.erga-biodiversity.eu/" target="_blank">European Reference Genome Atlas (ERGA)</a>
initiative is a pan-European scientific response to current threats to biodiversity. Reference genomes provide the most complete insight into the genetic basis that forms each species and represent a powerful resource in understanding how biodiversity functions. With approximately one fifth of the ~200,000 European species at risk of extinction, we need to act fast and together to generate high-quality complete genome resources in large scale.
    </p>
<br/>

<h3 id="asg_testimonial">
    <img width="170px" src="/assets/img/ASG_logo_nowords_transparent-1-300x300.png"
        class="float-end ps-4 darkmode-image" />
    Aquatic Symbiosis Genomics
    <a href="#asg_testimonial" class="header-link"><span class="fas fa-link" aria-hidden="true"></span></a>
</h3>
<p>The <a href="https://www.aquaticsymbiosisgenomics.org/" target="_blank">Aquatic Symbiosis Genomics (ASG)</a>
project is sequencing the genomes of symbiotic systems.
The project seeks to provide the genomic foundations needed by scientists to answer key questions about the ecology and evolution of symbiosis in marine and freshwater species, where at least one partner is a microbe.

ASG is jointly funded by the Wellcome Sanger Institute and the <a href="https://www.moore.org/">Gordon and Betty Moore Foundation</a>,
with ten global partners acting as hubs for different groups of symbiotic organisms.
    </p>
<br/>

<h3 id="aegis_testimonial">
    Ancient Environmental Genomics Initiative for Sustainability
    <a href="#aegis_testimonial" class="header-link"><span class="fas fa-link" aria-hidden="true"></span></a>
</h3>
<p>The <a href="https://globe.ku.dk/research/ancient-environmental-genomics-initiative-for-sustainability/" target="_blank">Ancient Environmental Genomics Initiative for Sustainability (AEGIS)</a>,
jointly funded by the NovoNordisk Foundation and the Wellcome Trust,
is a global consortium led by the Globe Institute at the University of Copenhagen.
AEGIS aims to develop the essential science and methodology to use ancient environmental DNA (eDNA) – coupled with other ancient and modern biomolecule-based approaches – to identify important organismal associations and genetic adaptations in natural and agro-ecosystems that will improve future food security under climate change.
    </p>
<br/>

<h3 id="bat1k_testimonial">
    <img width="170px" src="/assets/img/Mina-logo-340-156.png"
        class="float-end ps-4 darkmode-image" />
    Bat1K
    <a href="#bat1k_testimonial" class="header-link"><span class="fas fa-link" aria-hidden="true"></span></a>
</h3>
<p><a href="https://bat1k.com/" /target="_blank">Bat1K</a>
is an initiative to sequence the genomes of all living bat species, approximately 1400 species in total. The main goal of this consortium is to uncover the genes and genetic mechanisms behind the unusual adaptations of bats, essentially mine the bat genome to uncover their secrets. Using the newest of our genetic tools, we will deep sequence the blue print and genetic code of every species of bat in the world. It took over 13 years and $3 billion US dollars to sequence the first human genome, and given the great advances in the field it is now much faster and cheaper. We must put these fantastic technological advances to good use and push them to achieve their full potential. Imagine uncovering the secret of longer health-spans, flight, echolocation and disease resistance hidden in the bat genome.
    </p>
<br/>

<h3 id="bga_testimonial">
    <img width="170px" src="/assets/img/BGA24_final_2.png"
        class="float-end ps-4 darkmode-image" />
    BioDiversity Genomics Academy
    <a href="#bga_testimonial" class="header-link"><span class="fas fa-link" aria-hidden="true"></span></a>
</h3>
<p><a href="https://thebgacademy.org/" /target="_blank">BioDiversity Genomics Academy</a>
is a series of free, open to all, online-only, short and interactive sessions on how to use the bioinformatics tools and approaches that underpin the <a href="https://www.earthbiogenome.org/">Earth BioGenome Project</a> and the field as a whole.
    </p>
<br/>


<div class="clearfix"></div>

<?php include '../includes/footer.php';
