<?php
// ─── torecon Pillar-Definitionen ──────────────────────────────────────────────
// Single Source of Truth (innerhalb intern.torecon.de):
//   Diese Datei. Bei Änderungen an den Pillars wird **nur hier** editiert.
//
// Pflege-Quelle (Source-of-Truth Cross-Subsite):
//   ~/Obsidian/MyBrain/03_Development/_projects/linkedin/pillars/index.md
//   Sync ist manuell — bei Änderung dort wird hier nachgezogen + auf
//   torecon.de/js/topics.js gespiegelt.
//
// Konsumenten dieser Datei:
//   - linkedin.php       (Generator + Series-Dropdown + Hashtag-Pool)
//   - settings.php       (Pillar-Auswahl-Dropdown)
//   - topics_data.php    (öffentlicher JSON-Endpoint für torecon.de)

if (!function_exists('torecon_pillars')) {
    /**
     * Liefert die kanonische Liste aller aktiven Pillars (Pillar 1–N).
     * Reihenfolge entspricht der Pillar-Doc.
     * @return array
     */
    function torecon_pillars() {
        return array(
            array(
                'id'       => 'geldpolitik',
                'icon'     => '📈',
                'label_de' => 'Geldpolitik & Zinsen',
                'sub_de'   => 'EZB, Leitzins, Inflation',
                'label_en' => 'Monetary Policy & Rates',
                'sub_en'   => 'ECB, key rates, inflation',
                'hashtags' => '#Geldpolitik #EZB #Zinswende #Treasury #ALM #Bankbilanz #Inflation',
            ),
            array(
                'id'       => 'cx',
                'icon'     => '📱',
                'label_de' => 'Digitale Customer Experience & Omnichannel',
                'sub_de'   => 'CX-Strategie, App, digitale Filiale',
                'label_en' => 'Digital Customer Experience & Omnichannel',
                'sub_en'   => 'CX strategy, app, digital branch',
                'hashtags' => '#DigitaleFiliale #Omnichannel #BankCX #Onboarding #MobileBanking #CustomerExperience #BankingApp',
            ),
            array(
                'id'       => 'regulierung',
                'icon'     => '⚖️',
                'label_de' => 'Regulierung & Compliance',
                'sub_de'   => 'Basel IV, BaFin, EBA',
                'label_en' => 'Regulation & Compliance',
                'sub_en'   => 'Basel IV, BaFin, EBA',
                'hashtags' => '#BaselIV #BaFin #EBA #Bankenaufsicht #Compliance #RegTech #MaRisk',
            ),
            array(
                'id'       => 'digitalisierung',
                'icon'     => '🤖',
                'label_de' => 'Digitalisierung & KI',
                'sub_de'   => 'Fintech, AI, Kreditscoring',
                'label_en' => 'Digitalisation & AI',
                'sub_en'   => 'Fintech, AI, credit scoring',
                'hashtags' => '#KIimBanking #FintechDACH #Kreditscoring #GenAIBanking #Bankautomation #FraudDetection #BankIT',
            ),
            array(
                'id'       => 'esg',
                'icon'     => '🌱',
                'label_de' => 'Nachhaltigkeit & ESG',
                'sub_de'   => 'CSRD, Green Finance, Taxonomie',
                'label_en' => 'Sustainability & ESG',
                'sub_en'   => 'CSRD, green finance, taxonomy',
                'hashtags' => '#CSRD #GreenFinance #EUTaxonomie #SFDR #KlimaRisiko #ESGReporting #NachhaltigeBanken',
            ),
            array(
                'id'       => 'datenplattform',
                'icon'     => '📊',
                'label_de' => 'Datenplattform für KI',
                'sub_de'   => 'AI-Readiness, Data Mesh, Governance',
                'label_en' => 'Data Platform for AI',
                'sub_en'   => 'AI readiness, data mesh, governance',
                'hashtags' => '#Datenplattform #AIReadiness #DataMesh #Lakehouse #DataQuality #DataGovernance #FeatureStore #BankIT',
            ),
            array(
                'id'       => 'agentic-ai',
                'icon'     => '🧩',
                'label_de' => 'Agentic AI in der Praxis',
                'sub_de'   => 'Agent-Orchestrierung, Memory, Tool-Use',
                'label_en' => 'Agentic AI in Practice',
                'sub_en'   => 'Agent orchestration, memory, tool use',
                'hashtags' => '#AgenticAI #AIagents #LLMOrchestration #MCP #AgentEngineering #BuildInPublic #KIPraxis #PromptEngineering',
            ),
            array(
                'id'       => 'legacy',
                'icon'     => '🔄',
                'label_de' => 'Legacy Transformation',
                'sub_de'   => 'Kernbanksysteme, Migration, Modernisierung',
                'label_en' => 'Legacy Transformation',
                'sub_en'   => 'Core banking, migration, modernisation',
                'hashtags' => '#Kernbankmigration #LegacyTransformation #Coreplattform #StranglerFig #BankIT #Datenmigration #PlattformWechsel',
            ),
            array(
                'id'       => 'pricing',
                'icon'     => '💼',
                'label_de' => 'Pricing',
                'sub_de'   => 'Outcome-Based, Sprint-Tier, Quality-Gates',
                'label_en' => 'Pricing',
                'sub_en'   => 'Outcome-based, sprint-tier, quality gates',
                'hashtags' => '#PricingStrategy #AgenticCoding #Gainshare #SprintTier #QualityGates #6PhasenModell #ITStrategy',
            ),
        );
    }

    /**
     * Topic-String wie er in linkedin_settings.json / im Generator-Prompt steht:
     * "<label_de> (<sub_de>)" — z.B. "Geldpolitik & Zinsen (EZB, Leitzins, Inflation)".
     */
    function torecon_pillar_topic_string($pillar) {
        return $pillar['label_de'] . ' (' . $pillar['sub_de'] . ')';
    }

    /**
     * Lookup Hashtag-Pool über das label_de-Prefix des Topic-Strings.
     */
    function torecon_pillar_hashtag_pool($topic_string) {
        $key = trim($topic_string);
        $paren = strpos($key, '(');
        if ($paren !== false) $key = trim(substr($key, 0, $paren));
        foreach (torecon_pillars() as $p) {
            if ($p['label_de'] === $key) return $p['hashtags'];
        }
        return '#Banking #Bankenstrategie';
    }

    /**
     * Liste aller Topic-Strings (für Dropdowns in linkedin.php / settings.php).
     */
    function torecon_pillar_topic_strings() {
        $out = array();
        foreach (torecon_pillars() as $p) {
            $out[] = torecon_pillar_topic_string($p);
        }
        return $out;
    }
}
